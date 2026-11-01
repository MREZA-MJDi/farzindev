#!/usr/bin/env bash
set -Eeuo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
DEPLOY_USER="${DEPLOY_USER:-deploy}"

if [[ "$#" -eq 0 ]]; then
    echo "Usage: bash scripts/artisan-as-deploy.sh <artisan-command> [arguments...]" >&2
    exit 2
fi

if [[ "${EUID}" -eq 0 ]]; then
    if ! command -v runuser >/dev/null 2>&1; then
        echo "runuser is required to execute Artisan as $DEPLOY_USER." >&2
        exit 1
    fi
    if ! getent passwd "$DEPLOY_USER" >/dev/null; then
        echo "Deployment user '$DEPLOY_USER' does not exist. Create it or set DEPLOY_USER." >&2
        exit 1
    fi

    exec runuser -u "$DEPLOY_USER" -- env APP_ROOT="$APP_ROOT" /bin/bash -c '
        set -Eeuo pipefail
        umask 0007
        cd "$APP_ROOT"
        exec php artisan "$@"
    ' artisan-as-deploy "$@"
fi

CURRENT_USER="$(id -un)"
if [[ "$CURRENT_USER" != "$DEPLOY_USER" ]]; then
    echo "Run as root (the wrapper will switch users) or as '$DEPLOY_USER'; current user is '$CURRENT_USER'." >&2
    exit 1
fi

umask 0007
cd "$APP_ROOT"
exec php artisan "$@"
