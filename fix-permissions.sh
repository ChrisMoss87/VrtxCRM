#!/bin/bash

echo "Fixing Laravel Storage Permissions"
echo "==================================="
echo ""

# Fix storage permissions
echo "Setting storage directory permissions..."
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage

# Fix bootstrap/cache permissions
echo "Setting bootstrap/cache permissions..."
sudo chown -R www-data:www-data bootstrap/cache
sudo chmod -R 775 bootstrap/cache

# Add current user to www-data group (so you can write to these directories too)
echo "Adding $USER to www-data group..."
sudo usermod -a -G www-data $USER

# Set proper directory permissions
echo "Setting directory permissions..."
sudo find storage -type d -exec chmod 775 {} \;
sudo find bootstrap/cache -type d -exec chmod 775 {} \;

# Set proper file permissions
echo "Setting file permissions..."
sudo find storage -type f -exec chmod 664 {} \;
sudo find bootstrap/cache -type f -exec chmod 664 {} \;

echo ""
echo "✅ Permissions fixed!"
echo ""
echo "⚠️  Important: You may need to log out and back in for group changes to take effect."
echo ""
echo "To test if it worked, try:"
echo "  touch storage/logs/test.log"
echo ""
