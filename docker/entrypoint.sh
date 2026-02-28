#!/bin/bash
set -e

# Ensure required directories exist
mkdir -p /var/www/html/var /var/www/html/scan

# Fix permissions for directories Apache needs to write to
chown -R www-data:www-data /var/www/html/var /var/www/html/scan

# First-run setup: install dependencies and clear cache
# Stamp file is inside the vendor volume so it resets when vendor volume is recreated
if [ ! -f /var/www/html/vendor/.docker-installed ]; then
    echo ">>> First run detected. Installing dependencies..."
    composer install --no-interaction --optimize-autoloader --working-dir=/var/www/html
    echo ">>> Clearing Symfony cache..."
    php /var/www/html/bin/console cache:clear
    # Re-fix permissions after composer install and cache clear
    chown -R www-data:www-data /var/www/html/var
    touch /var/www/html/vendor/.docker-installed
    echo ">>> First-run setup complete."
fi

# Database initialization: create schema and load fixtures if the DB is empty
# Uses a separate stamp to avoid re-seeding on vendor volume rebuild
if [ ! -f /var/www/html/var/.db-initialized ]; then
    echo ">>> Initializing database schema..."
    php /var/www/html/bin/console doctrine:schema:update --force --no-interaction
    echo ">>> Loading fixtures..."
    php /var/www/html/bin/console doctrine:fixtures:load --no-interaction
    touch /var/www/html/var/.db-initialized
    echo ">>> Database initialization complete."
fi

# Hand off to Apache
exec apache2-foreground

