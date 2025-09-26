#!/bin/bash
set -e

# Start php-fpm in background (official php image)
php-fpm -D

# Give php-fpm a moment to start
sleep 2

# Test nginx config and start nginx in foreground (CMD will run it)
nginx -t || cat /var/log/nginx/error.log

# Exec CMD (nginx -g "daemon off;") provided by Dockerfile
exec "$@"
