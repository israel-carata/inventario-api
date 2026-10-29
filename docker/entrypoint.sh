#!/bin/sh
# Se ejecuta cada vez que Render arranca el contenedor.
set -e
cd /var/www/html

# 1) Puerto que asigna Render (por defecto 10000)
PORT="${PORT:-10000}"
sed -ri "s/Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# 2) APP_KEY: Render genera una clave base64 sin el prefijo que Laravel espera
if [ -n "$APP_KEY" ] && [ "${APP_KEY#base64:}" = "$APP_KEY" ]; then
    export APP_KEY="base64:${APP_KEY}"
fi

# 3) Sin base externa -> SQLite como respaldo
if [ -z "$DB_URL" ] && [ -z "$DB_HOST" ]; then
    export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
    touch database/database.sqlite
    chown www-data:www-data database/database.sqlite
fi

# 4) Laravel antiguo sin .env -> crear uno vacío para que artisan no falle
[ -f .env ] || touch .env

php artisan package:discover --ansi || true
php artisan storage:link || true

# 5) Migraciones automáticas (y datos de ejemplo si SEED_ON_DEPLOY=true)
php artisan migrate --force
if [ "$SEED_ON_DEPLOY" = "true" ]; then
    php artisan db:seed --force || echo "Seeder falló (se continúa)"
fi

# 6) Cachear configuración y vistas. config:cache "congela" las variables de entorno.
php artisan config:cache
php artisan view:cache || true
php artisan route:cache || echo "route:cache no disponible (rutas con closures), se continúa"

chown -R www-data:www-data storage bootstrap/cache

exec apache2-foreground
