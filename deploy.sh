#!/bin/bash
set -e

# Configuration
DEPLOY_DIR="${DEPLOY_PATH:-/var/www/my-granduation-project}"

echo "=== Deployment started at $(date) ==="

# Pull latest changes
echo "Pulling latest code..."
cd "$DEPLOY_DIR"
export GIT_SSH_COMMAND="ssh -i ~/.ssh/github_deploy -o IdentitiesOnly=yes -o StrictHostKeyChecking=no"
git pull origin master

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Run migrations
echo "Running migrations..."
php artisan migrate

# Clear and cache configurations
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Set permissions
echo "Setting permissions..."
# chown -R www-data:www-data "$DEPLOY_DIR"
chmod -R 755 "$DEPLOY_DIR"
chmod -R 775 "$DEPLOY_DIR/storage"
chmod -R 775 "$DEPLOY_DIR/bootstrap/cache"

# Restart queue workers if supervisor is installed
if command -v supervisorctl &> /dev/null; then
    echo "Restarting queue workers..."
    supervisorctl restart all || echo "Supervisor not configured or failed to restart"
fi

echo "=== Deployment completed successfully! ==="
