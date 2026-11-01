#!/usr/bin/env bash
set -Eeuo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"

if [[ "${EUID}" -ne 0 ]]; then
    echo "Run this recovery as root: sudo bash scripts/restore-vps-runtime.sh" >&2
    exit 1
fi

cd "$APP_ROOT"

echo "[1/6] Repairing Farzin source, .env, storage and bootstrap cache permissions..."
bash "$APP_ROOT/scripts/repair-vps-permissions.sh"

echo "[2/6] Clearing stale Laravel caches as the deployment user..."
bash "$APP_ROOT/scripts/artisan-as-deploy.sh" optimize:clear

echo "[3/6] Rebuilding configuration cache..."
bash "$APP_ROOT/scripts/artisan-as-deploy.sh" config:cache

echo "[4/6] Rebuilding route cache..."
bash "$APP_ROOT/scripts/artisan-as-deploy.sh" route:cache

echo "[5/6] Rebuilding compiled Blade views..."
bash "$APP_ROOT/scripts/artisan-as-deploy.sh" view:cache

echo "[6/6] Running production preflight..."
bash "$APP_ROOT/scripts/artisan-as-deploy.sh" farzin:preflight

echo "Farzin runtime recovery completed. No migrations, seeders, payment actions or database writes were run."
