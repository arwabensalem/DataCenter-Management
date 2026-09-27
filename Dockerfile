# GreenDC Advisor — image tout-en-un (PHP Apache + MariaDB)
# Conçue pour le plan gratuit Render (512 Mo) : app web complète.
# Le service RAG Python n'est PAS inclus ici (trop lourd pour le free tier).
# L'AI Advisor fonctionne en mode fallback déterministe ; branchez RAG_API_URL si besoin.

FROM php:8.2-apache-bookworm

ENV DEBIAN_FRONTEND=noninteractive \
    APP_ENV=production \
    APP_DEBUG=0 \
    APP_URL= \
    DB_HOST=127.0.0.1 \
    DB_PORT=3306 \
    DB_NAME=greendc_advisor \
    DB_USER=greendc \
    DB_PASS=greendc \
    SKIP_RAG=1

RUN apt-get update && apt-get install -y --no-install-recommends \
        mariadb-server \
        mariadb-client \
        libzip-dev \
        unzip \
        curl \
    && docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html
COPY deploy/htaccess.docker /var/www/html/.htaccess
COPY deploy/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh \
    && chown -R www-data:www-data /var/www/html \
    && sed -i 's#AllowOverride None#AllowOverride All#g' /etc/apache2/apache2.conf \
    && rm -rf /var/www/html/rag/.venv \
              /var/www/html/rag/storage \
              /var/www/html/rag/logs \
              /var/www/html/.git \
    || true

# DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
  CMD curl -fsS "http://127.0.0.1:${PORT:-8080}/" >/dev/null || exit 1

CMD ["/entrypoint.sh"]
