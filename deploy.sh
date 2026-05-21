#!/bin/bash
set -e

# Color variables for beautiful output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}====================================================${NC}"
echo -e "${GREEN}      STARTING DEPLOYMENT PROCESS (LARAVEL APP)      ${NC}"
echo -e "${YELLOW}====================================================${NC}"

# Ensure we are in the project directory
# (Customize this path if your folder layout is different)
cd "$(dirname "$0")"

# 1. Enable Maintenance Mode
echo -e "\n${YELLOW}[1/6] Activating Maintenance Mode...${NC}"
php artisan down || echo -e "${RED}Warning: Application already down or failed to enter maintenance mode.${NC}"

# 2. Pull latest changes from git
echo -e "\n${YELLOW}[2/6] Pulling latest code from Git...${NC}"
git pull origin main

# 3. Install production dependencies
echo -e "\n${YELLOW}[3/6] Installing production dependencies via Composer...${NC}"
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# 4. Run database migrations
echo -e "\n${YELLOW}[4/6] Running database migrations...${NC}"
php artisan migrate --force

# 5. Clear and Cache Configurations, Routes, and Views
echo -e "\n${YELLOW}[5/6] Optimizing Laravel configuration caches...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# (Optional) If you use Laravel Queue Workers, restart them
# echo -e "\n${YELLOW}[5.5/6] Restarting Laravel Queue workers...${NC}"
# php artisan queue:restart || true

# 6. Disable Maintenance Mode
echo -e "\n${YELLOW}[6/6] Deactivating Maintenance Mode (Application is now LIVE)...${NC}"
php artisan up

# Fix storage permissions if necessary
echo -e "\n${YELLOW}Setting storage and bootstrap/cache permissions...${NC}"
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || echo -e "${YELLOW}Notice: Web server user permission step skipped (not running as root).${NC}"

echo -e "\n${GREEN}====================================================${NC}"
echo -e "${GREEN}         DEPLOYMENT COMPLETED SUCCESSFULLY!          ${NC}"
echo -e "${GREEN}         Application is back online!                 ${NC}"
echo -e "${GREEN}====================================================${NC}"
