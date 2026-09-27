#!/bin/bash
set -euo pipefail

echo "[entrypoint] GreenDC Advisor — démarrage"

# MariaDB
if [ ! -d "/var/lib/mysql/mysql" ]; then
  echo "[entrypoint] Initialisation MariaDB..."
  if command -v mariadb-install-db >/dev/null 2>&1; then
    mariadb-install-db --user=mysql --datadir=/var/lib/mysql >/dev/null
  else
    mysql_install_db --user=mysql --datadir=/var/lib/mysql >/dev/null
  fi
fi

if command -v mysqld_safe >/dev/null 2>&1; then
  mysqld_safe --datadir=/var/lib/mysql &
else
  mariadbd --user=mysql --datadir=/var/lib/mysql &
fi
# Attendre MySQL
for i in $(seq 1 60); do
  if mysqladmin ping -h127.0.0.1 --silent 2>/dev/null; then
    break
  fi
  sleep 1
done

DB_NAME="${DB_NAME:-greendc_advisor}"
DB_USER="${DB_USER:-greendc}"
DB_PASS="${DB_PASS:-greendc}"

mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'%';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

# Import schéma si tables absentes
TABLES=$(mysql -N -u root -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${DB_NAME}';")
if [ "${TABLES}" = "0" ]; then
  echo "[entrypoint] Import SQL..."
  mysql -u root "${DB_NAME}" < /var/www/html/database/greendc_advisor.sql
  if [ -f /var/www/html/database/migration_modules_avances.sql ]; then
    mysql -u root "${DB_NAME}" < /var/www/html/database/migration_modules_avances.sql || true
  fi
fi

# Apache écoute le port fourni par Render
PORT="${PORT:-8080}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf

export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME DB_USER DB_PASS
export APP_URL="${APP_URL:-}"
export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-0}"
export RAG_API_URL="${RAG_API_URL:-}"
export CO2_KG_PER_KWH="${CO2_KG_PER_KWH:-0.55}"

# Injecte les variables dans Apache + fichier .env lu par PHP
cat >/etc/apache2/conf-enabled/greendc-env.conf <<EOF
SetEnv DB_HOST ${DB_HOST}
SetEnv DB_PORT ${DB_PORT}
SetEnv DB_NAME ${DB_NAME}
SetEnv DB_USER ${DB_USER}
SetEnv DB_PASS ${DB_PASS}
SetEnv APP_ENV ${APP_ENV}
SetEnv APP_DEBUG ${APP_DEBUG}
SetEnv APP_URL "${APP_URL}"
SetEnv RAG_API_URL "${RAG_API_URL}"
SetEnv CO2_KG_PER_KWH ${CO2_KG_PER_KWH}
PassEnv DB_HOST DB_PORT DB_NAME DB_USER DB_PASS APP_ENV APP_DEBUG APP_URL RAG_API_URL CO2_KG_PER_KWH
EOF

cat >/var/www/html/.env <<EOF
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT}
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}
APP_ENV=${APP_ENV}
APP_DEBUG=${APP_DEBUG}
APP_URL=${APP_URL}
RAG_API_URL=${RAG_API_URL}
CO2_KG_PER_KWH=${CO2_KG_PER_KWH}
EOF
chown www-data:www-data /var/www/html/.env
chmod 640 /var/www/html/.env

# RAG API optionnel (léger : sans recharger les embeddings lourds si SKIP_RAG=1)
if [ "${SKIP_RAG:-0}" != "1" ] && [ -d /opt/rag ]; then
  echo "[entrypoint] Démarrage RAG API (mode fallback LLM si Ollama absent)..."
  cd /opt/rag
  # shellcheck disable=SC1091
  if [ -f /opt/rag/.venv/bin/activate ]; then
    # noqa
    . /opt/rag/.venv/bin/activate
  fi
  uvicorn rag.api:app --host 127.0.0.1 --port 8000 &
fi

echo "[entrypoint] Apache sur le port ${PORT}"
exec apache2-foreground
