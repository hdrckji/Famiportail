# famiPortail — image PHP + Apache pour déploiement Railway
FROM php:8.2-apache

# Active la réécriture d'URL (utile pour .htaccess)
RUN a2enmod rewrite

# Force un seul MPM (prefork, celui attendu par mod_php) pour éviter
# l'erreur "More than one MPM loaded" au démarrage d'Apache
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true; a2enmod mpm_prefork

# Installe les en-têtes de dev SQLite (requis pour compiler pdo_sqlite),
# puis les extensions PHP requises par famiCom
RUN apt-get update && apt-get install -y --no-install-recommends libsqlite3-dev && \
    docker-php-ext-install pdo pdo_sqlite && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Racine du serveur web
ENV APACHE_DOCUMENT_ROOT=/var/www/html

# Copie les fichiers du projet dans le conteneur
COPY . ${APACHE_DOCUMENT_ROOT}/

# Crée le dossier de données SQLite et donne les droits à Apache (www-data)
RUN mkdir -p ${APACHE_DOCUMENT_ROOT}/famicom/data && \
    chown -R www-data:www-data ${APACHE_DOCUMENT_ROOT}/famicom/data && \
    chmod -R 775 ${APACHE_DOCUMENT_ROOT}/famicom/data

# Entrypoint : configure Apache sur le port fourni par Railway ($PORT) au démarrage
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Port par défaut (Railway injecte $PORT au runtime et l'entrypoint s'y adapte)
ENV PORT=8080
EXPOSE 8080

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
