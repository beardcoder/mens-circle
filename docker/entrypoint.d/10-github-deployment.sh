#!/bin/sh

# GitHub Deployments API Integration for Coolify
# This script updates GitHub deployment status when the container starts
# Runs before the application starts and never blocks the startup
# CRITICAL: No set -e! Script must never fail and block container startup

echo "Starting GitHub Deployment status update..."

# Check if required environment variables are set
if [ -z "$GITHUB_TOKEN" ] || [ -z "$GITHUB_REPO" ]; then
    echo "⚠️  GitHub Deployment update skipped: GITHUB_TOKEN or GITHUB_REPO not set"
    exit 0
fi

# Set defaults for optional variables
GITHUB_REF="${GITHUB_REF:-main}"
DEPLOYMENT_ENVIRONMENT="${DEPLOYMENT_ENVIRONMENT:-production}"
DEPLOYMENT_URL="${APP_URL:-https://example.com}"
DEPLOYMENT_LOG_URL="${DEPLOYMENT_LOG_URL:-$DEPLOYMENT_URL}"

# Determine if this is a production environment
if [ "$DEPLOYMENT_ENVIRONMENT" = "production" ] || [ "$DEPLOYMENT_ENVIRONMENT" = "prod" ]; then
    IS_PRODUCTION="true"
    IS_TRANSIENT="false"
else
    IS_PRODUCTION="false"
    IS_TRANSIENT="true"
fi

# Extract owner and repo
REPO_OWNER=$(echo "$GITHUB_REPO" | cut -d'/' -f1)
REPO_NAME=$(echo "$GITHUB_REPO" | cut -d'/' -f2)

# GitHub API base URL
GITHUB_API="https://api.github.com"

# Set timeout for API calls (in seconds)
TIMEOUT=10

echo "📦 Repository: $GITHUB_REPO"
echo "🌿 Ref: $GITHUB_REF"
echo "🌍 Environment: $DEPLOYMENT_ENVIRONMENT (production=$IS_PRODUCTION, transient=$IS_TRANSIENT)"
echo "🔗 URL: $DEPLOYMENT_URL"

# Function to make GitHub API calls with timeout and error handling
github_api_call() {
    local method="$1"
    local endpoint="$2"
    local data="$3"

    curl -s -m "$TIMEOUT" \
        -X "$method" \
        -H "Accept: application/vnd.github+json" \
        -H "Authorization: Bearer $GITHUB_TOKEN" \
        -H "X-GitHub-Api-Version: 2022-11-28" \
        "$GITHUB_API$endpoint" \
        ${data:+-d "$data"} || echo '{"error": "API call failed"}'
}

# Function to safely extract JSON values using jq with fallback
json_extract() {
    local json="$1"
    local key="$2"

    # Try jq first (more reliable)
    if command -v jq >/dev/null 2>&1; then
        echo "$json" | jq -r "$key" 2>/dev/null || echo ""
    else
        # Fallback to grep (less reliable but works without jq)
        echo "$json" | grep -o "\"${key}\":[0-9]*" 2>/dev/null | head -1 | cut -d':' -f2 || echo ""
    fi
}

# Function to deactivate old deployments for the same environment
deactivate_old_deployments() {
    local current_deployment_id="$1"

    echo "🔍 Checking for old active deployments..."

    # Get all deployments for this environment (never fail)
    DEPLOYMENTS_RESPONSE=$(github_api_call "GET" "/repos/$REPO_OWNER/$REPO_NAME/deployments?environment=$DEPLOYMENT_ENVIRONMENT&per_page=10" || echo "[]")

    # Check if we got a valid response (should be a JSON array)
    if ! echo "$DEPLOYMENTS_RESPONSE" | grep -q '\[' 2>/dev/null; then
        echo "⚠️  Could not fetch existing deployments (might be empty or API error)"
        return 0
    fi

    # Extract deployment IDs (excluding the current one) - never fail
    if command -v jq >/dev/null 2>&1; then
        OLD_DEPLOYMENT_IDS=$(echo "$DEPLOYMENTS_RESPONSE" | jq -r '.[].id' 2>/dev/null | grep -v "^${current_deployment_id}$" 2>/dev/null || true)
    else
        OLD_DEPLOYMENT_IDS=$(echo "$DEPLOYMENTS_RESPONSE" | grep -o '"id":[0-9]*' 2>/dev/null | cut -d':' -f2 | grep -v "^${current_deployment_id}$" 2>/dev/null || true)
    fi

    if [ -z "$OLD_DEPLOYMENT_IDS" ]; then
        echo "✅ No old deployments to deactivate"
        return 0
    fi

    # Count old deployments
    OLD_COUNT=$(echo "$OLD_DEPLOYMENT_IDS" | wc -l 2>/dev/null || echo "0")
    echo "🧹 Found $OLD_COUNT old deployment(s) to deactivate"

    # Deactivate each old deployment (never fail)
    echo "$OLD_DEPLOYMENT_IDS" | while read -r old_id || true; do
        if [ -n "$old_id" ]; then
            echo "  → Deactivating deployment ID: $old_id"
            DEACTIVATE_RESPONSE=$(github_api_call "POST" "/repos/$REPO_OWNER/$REPO_NAME/deployments/$old_id/statuses" \
                "{
                    \"state\": \"inactive\",
                    \"description\": \"Replaced by deployment $current_deployment_id\"
                }" || echo "")

            # Check if deactivation was successful
            DEACTIVATE_STATE=""
            if command -v jq >/dev/null 2>&1; then
                DEACTIVATE_STATE=$(echo "$DEACTIVATE_RESPONSE" | jq -r '.state // empty' 2>/dev/null || echo "")
            else
                DEACTIVATE_STATE=$(echo "$DEACTIVATE_RESPONSE" | grep -o '"state":"[^"]*"' 2>/dev/null | cut -d'"' -f4 || echo "")
            fi

            if [ -n "$DEACTIVATE_STATE" ] && [ "$DEACTIVATE_STATE" = "inactive" ]; then
                echo "    ✅ Deployment $old_id set to inactive"
            else
                echo "    ⚠️  Failed to deactivate deployment $old_id (continuing anyway)"
            fi
        fi
    done || true

    echo "✅ Old deployments cleanup completed"
    return 0
}

# Create deployment (never fail)
echo "Creating GitHub deployment..."
DEPLOYMENT_RESPONSE=$(github_api_call "POST" "/repos/$REPO_OWNER/$REPO_NAME/deployments" \
    "{
        \"ref\": \"$GITHUB_REF\",
        \"environment\": \"$DEPLOYMENT_ENVIRONMENT\",
        \"production_environment\": $IS_PRODUCTION,
        \"transient_environment\": $IS_TRANSIENT,
        \"auto_merge\": false,
        \"required_contexts\": [],
        \"description\": \"Deployed via Coolify\"
    }" || echo "")

# Check if deployment was created successfully (never fail)
if echo "$DEPLOYMENT_RESPONSE" | grep -q '"id"' 2>/dev/null; then
    # Extract deployment ID using jq (robust) or fallback to grep
    if command -v jq >/dev/null 2>&1; then
        DEPLOYMENT_ID=$(echo "$DEPLOYMENT_RESPONSE" | jq -r '.id // empty' 2>/dev/null || echo "")
    else
        DEPLOYMENT_ID=$(echo "$DEPLOYMENT_RESPONSE" | grep -o '"id":[0-9]*' 2>/dev/null | head -1 | cut -d':' -f2 || echo "")
    fi

    if [ -n "$DEPLOYMENT_ID" ] && [ "$DEPLOYMENT_ID" != "null" ]; then
        echo "✅ Deployment created with ID: $DEPLOYMENT_ID"

        # Deactivate old deployments before setting new one to success
        # This is critical for production environments where auto_inactive doesn't work
        deactivate_old_deployments "$DEPLOYMENT_ID" || true

        # Create deployment status (never fail)
        echo "Setting deployment status to success..."
        STATUS_RESPONSE=$(github_api_call "POST" "/repos/$REPO_OWNER/$REPO_NAME/deployments/$DEPLOYMENT_ID/statuses" \
            "{
                \"state\": \"success\",
                \"environment\": \"$DEPLOYMENT_ENVIRONMENT\",
                \"log_url\": \"$DEPLOYMENT_LOG_URL\",
                \"environment_url\": \"$DEPLOYMENT_URL\",
                \"description\": \"Container started successfully\",
                \"auto_inactive\": true
            }" || echo "")

        # Check if status update was successful
        STATUS_STATE=""
        if command -v jq >/dev/null 2>&1; then
            STATUS_STATE=$(echo "$STATUS_RESPONSE" | jq -r '.state // empty' 2>/dev/null || echo "")
        else
            STATUS_STATE=$(echo "$STATUS_RESPONSE" | grep -o '"state":"[^"]*"' 2>/dev/null | cut -d'"' -f4 || echo "")
        fi

        if [ -n "$STATUS_STATE" ] && [ "$STATUS_STATE" = "success" ]; then
            echo "✅ Deployment status updated to success"
        else
            echo "⚠️  Failed to update deployment status (state: ${STATUS_STATE:-unknown})"
        fi
    else
        echo "⚠️  Failed to extract deployment ID"
    fi
else
    echo "⚠️  Failed to create deployment (API might be rate-limited or credentials invalid)"
fi

echo "GitHub Deployment update completed"
exit 0
