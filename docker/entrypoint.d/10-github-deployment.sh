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
