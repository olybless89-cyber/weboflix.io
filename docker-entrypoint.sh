#!/bin/bash
set -e

# Default to port 80 if PORT is not set by Railway
PORT="${PORT:-80}"

# Configure Apache to listen on Railway's dynamic $PORT
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

echo "Starting Weboflix Apache server on port ${PORT}..."

exec "$@"
