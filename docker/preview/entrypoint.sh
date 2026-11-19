#!/usr/bin/env bash
# Preview start-up: fresh SQLite database, demo accounts, then Apache on $PORT.
set -euo pipefail
cd /var/www/html

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY non impostata: configurarla nel pannello del servizio." >&2
  exit 1
fi
# Hosting dashboards often generate a plain base64 value: Laravel expects the "base64:" prefix.
case "${APP_KEY}" in base64:*) ;; *) export APP_KEY="base64:${APP_KEY}" ;; esac

# Public URL (Render provides RENDER_EXTERNAL_URL).
export APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-http://localhost:${PORT}}}"

# Apache listens on the port required by the platform.
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

touch "${DB_DATABASE}"
php artisan migrate --force --no-interaction
php artisan db:seed --class=PreviewSeeder --force --no-interaction
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache database

exec apache2-foreground
