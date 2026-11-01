#!/usr/bin/env bash
set -Eeuo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
FPM_USER="${FPM_USER:-www-data}"

if [[ "$#" -eq 0 ]]; then
    echo "Usage: bash scripts/artisan-as-fpm.sh <artisan-command> [arguments...]" >&2
    exit 2
fi

if [[ "${EUID}" -eq 0 ]]; then
    if ! command -v runuser >/dev/null 2>&1; then
        echo "runuser is required to execute Artisan as $FPM_USER." >&2
        exit 1
    fi
    if ! getent passwd "$FPM_USER" >/dev/null; then
        echo "PHP-FPM user '$FPM_USER' does not exist." >&2
        exit 1
    fi

    exec runuser -u "$FPM_USER" -- env APP_ROOT="$APP_ROOT" /bin/bash -c '
        set -Eeuo pipefail
        umask 0007
        cd "$APP_ROOT"
        exec php artisan "$@"
    ' artisan-as-fpm "$@"
fi

CURRENT_USER="$(id -un)"
if [[ "$CURRENT_USER" != "$FPM_USER" ]]; then
    echo "Run as root (the wrapper will switch users) or as '$FPM_USER'; current user is '$CURRENT_USER'." >&2
    exit 1
fi

umask 0007
cd "$APP_ROOT"
exec php artisan "$@"
