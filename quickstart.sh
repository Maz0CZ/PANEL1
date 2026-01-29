#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [ ! -d "${ROOT_DIR}/vendor" ]; then
  echo "Installing composer dependencies..."
  composer install
fi

echo "Initializing database..."
php "${ROOT_DIR}/bin/init_db.php" "${ROOT_DIR}/storage/db/panel.sqlite"

mkdir -p "${ROOT_DIR}/storage/logs" "${ROOT_DIR}/storage/sessions" "${ROOT_DIR}/storage/temp"

echo "Starting UltimatePanel on http://localhost:8000"
php -S 0.0.0.0:8000 -t "${ROOT_DIR}/public"
