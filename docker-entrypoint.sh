#!/bin/sh
# Configure Apache pour écouter sur le port fourni par Railway ($PORT),
# avec 8080 comme valeur de repli en local.
set -e

PORT="${PORT:-8080}"

# Met à jour le port d'écoute global
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf

# Met à jour le port du VirtualHost par défaut pour qu'il corresponde
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
