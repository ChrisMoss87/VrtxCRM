#!/bin/bash

# VrtxCRM Nginx Configuration Setup Script

set -e

echo "=================================================="
echo "VrtxCRM Nginx Multi-Tenant Configuration Setup"
echo "=================================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -eq 0 ]; then
    echo -e "${RED}✗ Don't run this script as root/sudo${NC}"
    echo "The script will prompt for sudo when needed"
    exit 1
fi

# Check if nginx is installed
if ! command -v nginx &> /dev/null; then
    echo -e "${RED}✗ Nginx is not installed${NC}"
    echo "Install it with: sudo apt install nginx"
    exit 1
fi

echo "1. Backing up existing nginx configuration..."
if [ -f /etc/nginx/sites-available/VrtxCRM ]; then
    sudo cp /etc/nginx/sites-available/VrtxCRM /etc/nginx/sites-available/VrtxCRM.backup.$(date +%Y%m%d_%H%M%S)
    echo -e "${GREEN}✓${NC} Backup created"
else
    echo -e "${YELLOW}⚠${NC} No existing config to backup"
fi

echo ""
echo "2. Copying new nginx configuration..."
sudo cp nginx-config.conf /etc/nginx/sites-available/VrtxCRM
echo -e "${GREEN}✓${NC} Configuration copied"

echo ""
echo "3. Disabling conflicting configurations..."
# Disable the other vrtx configs to avoid conflicts
for config in Vrtx vrtxCRM; do
    if [ -L /etc/nginx/sites-enabled/$config ]; then
        sudo rm /etc/nginx/sites-enabled/$config
        echo -e "${GREEN}✓${NC} Disabled $config"
    fi
done

echo ""
echo "4. Ensuring VrtxCRM is enabled..."
if [ ! -L /etc/nginx/sites-enabled/VrtxCRM ]; then
    sudo ln -s /etc/nginx/sites-available/VrtxCRM /etc/nginx/sites-enabled/VrtxCRM
    echo -e "${GREEN}✓${NC} VrtxCRM enabled"
else
    echo -e "${GREEN}✓${NC} Already enabled"
fi

echo ""
echo "5. Testing nginx configuration..."
if sudo nginx -t; then
    echo -e "${GREEN}✓${NC} Configuration is valid"
else
    echo -e "${RED}✗${NC} Configuration test failed"
    echo "Please check the error messages above"
    exit 1
fi

echo ""
echo "6. Reloading nginx..."
sudo systemctl reload nginx
echo -e "${GREEN}✓${NC} Nginx reloaded"

echo ""
echo "=================================================="
echo -e "${GREEN}✓ Setup Complete!${NC}"
echo "=================================================="
echo ""
echo "Your nginx is now configured for:"
echo "  • vrtxcrm.local (central domain)"
echo "  • *.vrtxcrm.local (tenant subdomains)"
echo "  • *.localhost (tenant subdomains)"
echo ""
echo "Test it:"
echo "  curl http://acme.vrtxcrm.local"
echo "  curl http://vrtxcrm.local"
echo ""
echo "View logs:"
echo "  sudo tail -f /var/log/nginx/vrtxcrm-access.log"
echo "  sudo tail -f /var/log/nginx/vrtxcrm-error.log"
echo ""
