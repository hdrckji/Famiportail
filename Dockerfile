# famiPortail — image PHP + Apache pour déploiement Railway
FROM php:8.2-apache

# Active la réécriture d'URL (utile pour .htaccess)
RUN a2enmod rewrite

# Force un seul MPM (prefork, celui attendu par mod_php) pour éviter
# l'erreur "More than one MPM loaded" au démarrage d'Apache.
# On supprime physiquement TOUS les liens MPM puis on n'active que prefork.
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf && \
    a2enmod mpm_prefork

# Extensions PHP :
#  - pdo_mysql : base partagée du portail (utilisateurs de famiformation)
#  - pdo_sqlite : encore utilisé par famiCom (annonces) — libsqlite3-dev requis
RUN apt-get update && apt-get install -y --no-install-recommends libsqlite3-dev && \
    docker-php-ext-install pdo pdo_mysql pdo_sqlite && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Racine du serveur web
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# Copie les fichiers du projet dans le conteneur
COPY . ${APACHE_DOCUMENT_ROOT}/

# Dossiers de données SQLite en écriture pour Apache (www-data) :
#  - data/          : base partagée du portail (utilisateurs, etc.)
#  - famicom/data/  : base des annonces famiCom
# À monter sur un volume Railway pour persister au-delà des redéploiements.
RUN mkdir -p ${APACHE_DOCUMENT_ROOT}/data ${APACHE_DOCUMENT_ROOT}/famicom/data && \
    chown -R www-data:www-data ${APACHE_DOCUMENT_ROOT}/data ${APACHE_DOCUMENT_ROOT}/famicom/data && \
    chmod -R 775 ${APACHE_DOCUMENT_ROOT}/data ${APACHE_DOCUMENT_ROOT}/famicom/data

# Bloque l'accès web DIRECT aux bases de données (les .htaccess ne suffisent pas :
# AllowOverride None par défaut). Personne ne peut télécharger un .sqlite.
RUN printf '<Directory %s/data>\n  Require all denied\n</Directory>\n<Directory %s/famicom/data>\n  Require all denied\n</Directory>\n' \
      "${APACHE_DOCUMENT_ROOT}" "${APACHE_DOCUMENT_ROOT}" \
      > /etc/apache2/conf-available/famiportail-securite.conf && \
    a2enconf famiportail-securite

# Entrypoint : configure Apache sur le port fourni par Railway ($PORT) au démarrage
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Port par défaut (Railway injecte $PORT au runtime et l'entrypoint s'y adapte)
ENV PORT=8080
EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
