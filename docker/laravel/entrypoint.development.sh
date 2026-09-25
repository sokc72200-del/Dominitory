#!/bin/bash
set -e

# If the application source (composer.json) is present, run normal startup.
if [ -f /var/www/html/composer.json ]; then
	echo "composer.json found, installing dependencies and starting app"
	composer install
	php artisan key:generate || true
	php artisan storage:link || true
	php artisan migrate || true
	php artisan serve --host=0.0.0.0 --port=8000
else
	echo "No composer.json found in /var/www/html — skipping install/migrate/serve"
	tail -f /dev/null
fi