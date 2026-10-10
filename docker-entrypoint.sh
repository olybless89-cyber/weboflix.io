#!/bin/bash
set -e

# Default to port 80 if PORT is not set by Railway
PORT="${PORT:-80}"

# Guarantee exactly one MPM (prefork) — fixes AH00534 "More than one MPM loaded"
rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf
a2enmod mpm_prefork >/dev/null 2>&1

# Configure Apache to listen on Railway's dynamic $PORT (safe to re-run)
sed -ri "s/^Listen [0-9]+$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# ---------------------------------------------------------------
# CRITICAL: Pass Docker/Railway environment variables into Apache
# mod_php does NOT automatically inherit the container environment.
# We write SetEnv directives into the VirtualHost so PHP can read
# them via getenv(), $_ENV, and $_SERVER.
# ---------------------------------------------------------------
ENVVARS_BLOCK=""
for var in \
    MYSQL_URL MYSQLHOST MYSQLPORT MYSQLUSER MYSQLPASSWORD MYSQLDATABASE \
    DATABASE_URL DB_HOST DB_PORT DB_USER DB_PASS DB_NAME \
    RAILWAY_PUBLIC_DOMAIN SITE_URL SITE_NAME \
    FLW_PUBLIC_KEY FLW_SECRET_KEY FLW_SECRET_HASH \
    PRICE_MONTHLY PRICE_YEARLY ADMIN_EMAIL DISPLAY_ERRORS PORT
do
    val="${!var}"
    if [ -n "$val" ]; then
        ENVVARS_BLOCK="${ENVVARS_BLOCK}    SetEnv ${var} ${val}\n"
    fi
done

# Inject before closing </VirtualHost> tag
if [ -n "$ENVVARS_BLOCK" ]; then
    sed -i "s|</VirtualHost>|${ENVVARS_BLOCK}</VirtualHost>|" \
        /etc/apache2/sites-available/000-default.conf
fi

echo "Starting Weboflix Apache server on port ${PORT}..."
apache2ctl -M 2>/dev/null | grep -i mpm || true

exec "$@"
