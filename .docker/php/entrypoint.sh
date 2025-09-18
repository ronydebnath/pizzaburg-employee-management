#!/bin/sh
set -e

# Set permissions for Laravel directories
# Use UID 1000 to match the host user (pburg)
chown -R 1000:1000 /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Ensure all project files have correct ownership
chown -R 1000:1000 /var/www

# Set permissions for web server (www-data) to write to logs and cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Fix log file permissions specifically
chmod 664 /var/www/storage/logs/laravel.log 2>/dev/null || true

# permissions for PHPMyAdmin
mkdir -p /sessions
chmod 777 /sessions

exec "$@"