#!/bin/sh
# Entrypoint Railway :
#  1) Normalise le MPM d'Apache (évite "More than one MPM loaded")
#  2) Fait écouter Apache sur le port fourni par Railway ($PORT)
set -e

PORT="${PORT:-8080}"

echo "=== [MPM] Déclarations LoadModule mpm_ AVANT correction ==="
grep -rEn "LoadModule[[:space:]]+mpm_" /etc/apache2/ 2>/dev/null || echo "(aucune)"

# a) Retire les symlinks MPM de mods-enabled
rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf

# b) Retire toute ligne "LoadModule mpm_..." ailleurs que dans mods-available
#    (mods-available garde les définitions officielles ; on n'y touche pas)
for f in /etc/apache2/apache2.conf \
         /etc/apache2/conf-enabled/*.conf \
         /etc/apache2/conf-available/*.conf \
         /etc/apache2/ports.conf; do
    [ -f "$f" ] && sed -ri 's/^[[:space:]]*LoadModule[[:space:]]+mpm_[A-Za-z_]+_module.*$//' "$f"
done

# c) Réactive un seul MPM : prefork (celui qu'exige mod_php)
a2enmod mpm_prefork >/dev/null 2>&1 || true

echo "=== [MPM] Déclarations LoadModule mpm_ APRÈS correction ==="
grep -rEn "LoadModule[[:space:]]+mpm_" /etc/apache2/ 2>/dev/null || echo "(aucune)"
echo "==========================================================="

# Port dynamique Railway : écoute + VirtualHost
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
