#!/usr/bin/env bash
set -Eeuo pipefail

APP_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd -P)"
FPM_USER="${FPM_USER:-www-data}"
FPM_GROUP="${FPM_GROUP:-www-data}"

if [[ "${EUID}" -ne 0 ]]; then
    echo "Run this repair as root: sudo bash scripts/repair-vps-permissions.sh" >&2
    exit 1
fi

if ! getent passwd "$FPM_USER" >/dev/null; then
    echo "PHP-FPM user '$FPM_USER' does not exist." >&2
    exit 1
fi

if ! getent group "$FPM_GROUP" >/dev/null; then
    echo "PHP-FPM group '$FPM_GROUP' does not exist." >&2
    exit 1
fi

# PHP-FPM must be able to read all application source and dependencies.
# find does not follow the public/storage symlink, so uploaded storage is not
# traversed accidentally while preparing the public document root.
make_tree_group_readable() {
    local path="$1"

    [[ -d "$path" ]] || return 0

    find "$path" -type d -exec chgrp "$FPM_GROUP" {} +
    find "$path" -type f -exec chgrp "$FPM_GROUP" {} +
    find "$path" -type d -exec chmod g+rx {} +
    find "$path" -type f -exec chmod g+r {} +
}

for relative_path in app bootstrap config database public resources routes vendor; do
    make_tree_group_readable "$APP_ROOT/$relative_path"
done

# Keep secrets private while allowing the PHP-FPM group to read .env.
if [[ -f "$APP_ROOT/.env" && ! -L "$APP_ROOT/.env" ]]; then
    chgrp "$FPM_GROUP" "$APP_ROOT/.env"
    chmod 0640 "$APP_ROOT/.env"
fi

# These are the only Laravel directories PHP-FPM must write to. The setgid
# bit keeps new files in the FPM group; 0660 + umask 0007 keeps generated
# manifests readable by PHP-FPM without exposing them to other users.
for relative_path in storage bootstrap/cache; do
    path="$APP_ROOT/$relative_path"
    install -d -o "$FPM_USER" -g "$FPM_GROUP" -m 2770 "$path"
    find "$path" -type d -exec chown "$FPM_USER:$FPM_GROUP" {} +
    find "$path" -type f -exec chown "$FPM_USER:$FPM_GROUP" {} +
    find "$path" -type d -exec chmod 2770 {} +
    find "$path" -type f -exec chmod 0660 {} +
done

echo "Farzin runtime permissions repaired for $FPM_USER:$FPM_GROUP."
echo "Application source: group-readable; .env: 0640; storage/bootstrap/cache: 2770 dirs, 0660 files."
echo "Project: $APP_ROOT"
