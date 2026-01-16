#!/bin/sh

# GitHub Deployments API Integration for Coolify
# This script updates GitHub deployment status when the container starts
# Runs before the application starts and never blocks the startup

set -e

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

# Extract owner and repo
REPO_OWNER=$(echo "$GITHUB_REPO" | cut -d'/' -f1)
REPO_NAME=$(echo "$GITHUB_REPO" | cut -d'/' -f2)

# GitHub API base URL
GITHUB_API="https://api.github.com"

# Set timeout for API calls (in seconds)
TIMEOUT=10

echo "📦 Repository: $GITHUB_REPO"
echo "🌿 Ref: $GITHUB_REF"
echo "🌍 Environment: $DEPLOYMENT_ENVIRONMENT"
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
        ${data:+-d "$data"} || echo "API call failed"
}

# Function to deactivate old deployments for the same environment
deactivate_old_deployments() {
    local current_deployment_id="$1"

    echo "🔍 Checking for old active deployments..."

    # Get all deployments for this environment
    DEPLOYMENTS_RESPONSE=$(github_api_call "GET" "/repos/$REPO_OWNER/$REPO_NAME/deployments?environment=$DEPLOYMENT_ENVIRONMENT&per_page=10")

    # Check if we got a valid response
    if ! echo "$DEPLOYMENTS_RESPONSE" | grep -q '"id"'; then
        echo "⚠️  Could not fetch existing deployments (might be empty or API error)"
        return 0
    fi

    # Extract deployment IDs (excluding the current one)
    OLD_DEPLOYMENT_IDS=$(echo "$DEPLOYMENTS_RESPONSE" | grep -o '"id":[0-9]*' | cut -d':' -f2 | grep -v "^${current_deployment_id}$")

    if [ -z "$OLD_DEPLOYMENT_IDS" ]; then
        echo "✅ No old deployments to deactivate"
        return 0
    fi

    # Count old deployments
    OLD_COUNT=$(echo "$OLD_DEPLOYMENT_IDS" | wc -l)
    echo "🧹 Found $OLD_COUNT old deployment(s) to deactivate"

    # Deactivate each old deployment
    echo "$OLD_DEPLOYMENT_IDS" | while read -r old_id; do
        if [ -n "$old_id" ]; then
            echo "  → Deactivating deployment ID: $old_id"
            DEACTIVATE_RESPONSE=$(github_api_call "POST" "/repos/$REPO_OWNER/$REPO_NAME/deployments/$old_id/statuses" \
                "{
                    \"state\": \"inactive\",
                    \"description\": \"Replaced by deployment $current_deployment_id\"
                }")

            if echo "$DEACTIVATE_RESPONSE" | grep -q '"state"'; then
                echo "    ✅ Deployment $old_id set to inactive"
            else
                echo "    ⚠️  Failed to deactivate deployment $old_id (continuing anyway)"
            fi
        fi
    done

    echo "✅ Old deployments cleanup completed"
}

# Create deployment
echo "Creating GitHub deployment..."
DEPLOYMENT_RESPONSE=$(github_api_call "POST" "/repos/$REPO_OWNER/$REPO_NAME/deployments" \
    "{
        \"ref\": \"$GITHUB_REF\",
        \"environment\": \"$DEPLOYMENT_ENVIRONMENT\",
        \"auto_merge\": false,
        \"required_contexts\": [],
        \"description\": \"Deployed via Coolify\"
    }")

# Check if deployment was created successfully
if echo "$DEPLOYMENT_RESPONSE" | grep -q '"id"'; then
    DEPLOYMENT_ID=$(echo "$DEPLOYMENT_RESPONSE" | grep -o '"id":[0-9]*' | head -1 | cut -d':' -f2)
    echo "✅ Deployment created with ID: $DEPLOYMENT_ID"

    # Deactivate old deployments before setting new one to success
    # This is critical for production environments where auto_inactive doesn't work
    deactivate_old_deployments "$DEPLOYMENT_ID"

    # Create deployment status
    echo "Setting deployment status to success..."
    STATUS_RESPONSE=$(github_api_call "POST" "/repos/$REPO_OWNER/$REPO_NAME/deployments/$DEPLOYMENT_ID/statuses" \
        "{
            \"state\": \"success\",
            \"environment\": \"$DEPLOYMENT_ENVIRONMENT\",
            \"environment_url\": \"$DEPLOYMENT_URL\",
            \"description\": \"Container started successfully\",
            \"auto_inactive\": true
        }")

    if echo "$STATUS_RESPONSE" | grep -q '"state"'; then
        echo "✅ Deployment status updated to success"
    else
        echo "⚠️  Failed to update deployment status"
    fi
else
    echo "⚠️  Failed to create deployment (API might be rate-limited or credentials invalid)"
fi

echo "GitHub Deployment update completed"
exit 0
