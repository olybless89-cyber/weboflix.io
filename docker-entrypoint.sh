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

echo "Starting Weboflix Apache server on port ${PORT}..."
apache2ctl -M 2>/dev/null | grep -i mpm || true

exec "$@"
