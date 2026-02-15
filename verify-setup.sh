#!/usr/bin/env bash
#
# Verification script for GraphicsMagick and German locale setup
# Usage: ./verify-setup.sh [docker|ddev]
#

set -e

MODE="${1:-ddev}"
COLOR_GREEN='\033[0;32m'
COLOR_RED='\033[0;31m'
COLOR_YELLOW='\033[1;33m'
COLOR_RESET='\033[0m'

echo "=========================================="
echo "GraphicsMagick & Locale Verification"
echo "Mode: $MODE"
echo "=========================================="
echo ""

# Set command prefix based on mode
if [ "$MODE" = "docker" ]; then
    # For production container - user needs to set CONTAINER_ID
    if [ -z "$CONTAINER_ID" ]; then
        echo -e "${COLOR_RED}Error: CONTAINER_ID environment variable not set${COLOR_RESET}"
        echo "Usage: CONTAINER_ID=<container_id> $0 docker"
        exit 1
    fi
    CMD="docker exec $CONTAINER_ID"
elif [ "$MODE" = "ddev" ]; then
    CMD="ddev exec"
else
    echo -e "${COLOR_RED}Error: Invalid mode '$MODE'. Use 'docker' or 'ddev'${COLOR_RESET}"
    exit 1
fi

# Test GraphicsMagick
echo "1. Testing GraphicsMagick installation..."
if $CMD which gm >/dev/null 2>&1; then
    VERSION=$($CMD gm version 2>/dev/null | head -1 || echo "unknown")
    echo -e "   ${COLOR_GREEN}✓${COLOR_RESET} GraphicsMagick found: $VERSION"
else
    echo -e "   ${COLOR_RED}✗${COLOR_RESET} GraphicsMagick not found"
    exit 1
fi
echo ""

# Test PHP extensions
echo "2. Testing PHP extensions..."
REQUIRED_EXTS=("gd" "exif" "imagick" "intl")
for ext in "${REQUIRED_EXTS[@]}"; do
    if $CMD php -m 2>/dev/null | grep -q "^$ext\$"; then
        echo -e "   ${COLOR_GREEN}✓${COLOR_RESET} PHP extension '$ext' loaded"
    else
        echo -e "   ${COLOR_RED}✗${COLOR_RESET} PHP extension '$ext' not found"
        exit 1
    fi
done
echo ""

# Test German locale
echo "3. Testing German locale (de_DE.UTF-8)..."
LOCALE_OUTPUT=$($CMD locale 2>/dev/null || echo "locale command failed")
if echo "$LOCALE_OUTPUT" | grep -q "de_DE.UTF-8"; then
    echo -e "   ${COLOR_GREEN}✓${COLOR_RESET} German locale (de_DE.UTF-8) is available"
else
    echo -e "   ${COLOR_YELLOW}⚠${COLOR_RESET} Warning: German locale not found in locale output"
fi
echo ""

# Test locale environment variables
echo "4. Testing locale environment variables..."
ENV_VARS=("LANG" "LANGUAGE" "LC_ALL")
for var in "${ENV_VARS[@]}"; do
    VALUE=$($CMD printenv "$var" 2>/dev/null || echo "")
    if [ -n "$VALUE" ]; then
        if echo "$VALUE" | grep -q "de_DE"; then
            echo -e "   ${COLOR_GREEN}✓${COLOR_RESET} $var=$VALUE"
        else
            echo -e "   ${COLOR_YELLOW}⚠${COLOR_RESET} $var=$VALUE (not German locale)"
        fi
    else
        echo -e "   ${COLOR_RED}✗${COLOR_RESET} $var not set"
    fi
done
echo ""

# Test available locales
echo "5. Listing available locales with 'de_DE'..."
LOCALES=$($CMD locale -a 2>/dev/null | grep -i "de_DE" || echo "none found")
if [ "$LOCALES" != "none found" ]; then
    echo "$LOCALES" | while read -r line; do
        echo -e "   ${COLOR_GREEN}✓${COLOR_RESET} $line"
    done
else
    echo -e "   ${COLOR_RED}✗${COLOR_RESET} No German locales found"
fi
echo ""

# Test PHP timezone
echo "6. Testing PHP timezone setting..."
TIMEZONE=$($CMD php -r "echo date_default_timezone_get();" 2>/dev/null || echo "unknown")
if [ "$TIMEZONE" = "Europe/Berlin" ]; then
    echo -e "   ${COLOR_GREEN}✓${COLOR_RESET} PHP timezone: $TIMEZONE"
else
    echo -e "   ${COLOR_YELLOW}⚠${COLOR_RESET} PHP timezone: $TIMEZONE (expected: Europe/Berlin)"
fi
echo ""

echo "=========================================="
echo -e "${COLOR_GREEN}Verification complete!${COLOR_RESET}"
echo "=========================================="
