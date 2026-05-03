#!/usr/bin/env bash

# Hostinger deployment template.
# انسخ هذا الملف على جهازك وعدّل placeholders محليًا فقط.
# Do not commit real credentials into this file.

set -euo pipefail

HOSTINGER_SSH_HOST="YOUR_HOST_HERE"
HOSTINGER_SSH_PORT="YOUR_PORT_HERE"
HOSTINGER_SSH_USER="YOUR_USER_HERE"
REMOTE_APP_PATH="/home/YOUR_USER/domains/mshabibanabil.com/app"
REMOTE_PUBLIC_PATH="/home/YOUR_USER/domains/mshabibanabil.com/public_html/staging"

# 1) Upload project files except Git and local secrets.
rsync -avz --delete \
  --exclude '.git' \
  --exclude '.env' \
  --exclude 'vendor' \
  --exclude 'node_modules' \
  -e "ssh -p ${HOSTINGER_SSH_PORT}" \
  ./ "${HOSTINGER_SSH_USER}@${HOSTINGER_SSH_HOST}:${REMOTE_APP_PATH}/"

# 2) Sync public web files to staging public_html folder.
ssh -p "${HOSTINGER_SSH_PORT}" "${HOSTINGER_SSH_USER}@${HOSTINGER_SSH_HOST}" "mkdir -p '${REMOTE_PUBLIC_PATH}' && rsync -av --delete '${REMOTE_APP_PATH}/web/public/' '${REMOTE_PUBLIC_PATH}/'"

# 3) Reminder: .env must be created manually on the server, outside GitHub.
echo "Deployment files uploaded. Now make sure server .env exists in the expected app path."
echo "Test staging URL after upload."
