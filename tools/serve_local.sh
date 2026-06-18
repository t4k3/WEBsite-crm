#!/usr/bin/env bash
# Avvia lo stack locale: MySQL + DB + schema + server PHP.
# Uso: ./tools/serve_local.sh   (poi apri http://localhost:8000)
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PORT="${PORT:-8000}"

# 1) MySQL attivo?
if ! mysqladmin --host=127.0.0.1 -uroot ping >/dev/null 2>&1; then
    echo "▶ Avvio MySQL..."
    brew services start mysql >/dev/null 2>&1 || mysql.server start >/dev/null 2>&1 || true
    for i in $(seq 1 30); do
        mysqladmin --host=127.0.0.1 -uroot ping >/dev/null 2>&1 && break
        sleep 1
    done
fi

# 2) Database + schema
echo "▶ Preparo il database 'wazlley'..."
mysql --host=127.0.0.1 -uroot -e "CREATE DATABASE IF NOT EXISTS wazlley CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql --host=127.0.0.1 -uroot wazlley < "$ROOT/sql/schema.sql"

# 3) Server PHP (esegue i .php e serve i file statici)
echo "✅ Pronto → http://localhost:$PORT   (ordine: /ordine.php · CRM: /admin/)"
echo "   Ctrl+C per fermare."
php -S "localhost:$PORT" -t "$ROOT"
