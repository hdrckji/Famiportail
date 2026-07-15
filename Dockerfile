# famiPortail — image PHP + Apache pour déploiement Railway
FROM php:8.2-apache

# Active la réécriture d'URL (utile pour .htaccess)
RUN a2enmod rewrite

# Installe les extensions PHP requises par famiCom (SQLite via PDO)
RUN docker-php-ext-install pdo pdo_sqlite

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
