#!/bin/bash
# ============================================================================
# Clear Laravel Caches After Rebranding
# ============================================================================
# This script clears all Laravel caches to ensure the Punjab Idiomas
# rebranding changes are immediately visible to users.
# ============================================================================

echo ""
echo "============================================================================"
echo "Clearing Laravel Caches After Rebranding"
echo "============================================================================"
echo ""

echo "[1/4] Clearing configuration cache..."
php artisan config:clear
if [ $? -ne 0 ]; then
    echo "ERROR: Failed to clear configuration cache"
    exit 1
fi
echo "Configuration cache cleared successfully!"
echo ""

echo "[2/4] Clearing view cache..."
php artisan view:clear
if [ $? -ne 0 ]; then
    echo "ERROR: Failed to clear view cache"
    exit 1
fi
echo "View cache cleared successfully!"
echo ""

echo "[3/4] Clearing route cache..."
php artisan route:clear
if [ $? -ne 0 ]; then
    echo "ERROR: Failed to clear route cache"
    exit 1
fi
echo "Route cache cleared successfully!"
echo ""

echo "[4/4] Clearing application cache..."
php artisan cache:clear
if [ $? -ne 0 ]; then
    echo "ERROR: Failed to clear application cache"
    exit 1
fi
echo "Application cache cleared successfully!"
echo ""

echo "============================================================================"
echo "All caches cleared successfully!"
echo "The Punjab Idiomas rebranding changes are now active."
echo "============================================================================"
echo ""

exit 0
