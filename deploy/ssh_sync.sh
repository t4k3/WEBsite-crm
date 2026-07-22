#!/usr/bin/env bash
# ==========================================
#  WAZLLEY - Deploy via rsync su SSH (easyname)
#  Affidabile: una connessione, solo i file cambiati.
#  Uso:
#    ./deploy/ssh_sync.sh preview   # mostra cosa caricherebbe (NON tocca il server)
#    ./deploy/ssh_sync.sh deploy    # carica le modifiche (chiede CONFERMO)
#  La password SSH va in deploy/.ssh_pass (gitignorato).
# ==========================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
source "$SCRIPT_DIR/.env"
: "${SSH_HOST:?}" "${SSH_PORT:?}" "${SSH_USER:?}" "${SSH_REMOTE_DIR:?}"

PASS_FILE="$SCRIPT_DIR/.ssh_pass"
[ -f "$PASS_FILE" ] || { echo "❌ Manca $PASS_FILE — mettici dentro la password SSH."; exit 1; }
SSHPASS="$(tr -d '\r\n' < "$PASS_FILE")"; export SSHPASS

SSH_CMD="ssh -p $SSH_PORT -o StrictHostKeyChecking=accept-new"

# File/cartelle che NON vanno mai in produzione
EXCLUDES=(
  --exclude '.git' --exclude 'deploy' --exclude '_live_backup'
  --exclude 'images' --exclude 'tools' --exclude 'assets/raw'
  --exclude 'config/db.local.php' --exclude '.gitignore'
  --exclude '.DS_Store' --exclude '*.zip' --exclude '*.md'
  --exclude 'QUICK_START.txt' --exclude 'check_wazlley.php'
)

case "${1:-}" in
  preview)
    echo "🔍 ANTEPRIMA (dry-run) → $SSH_USER@$SSH_HOST:$SSH_REMOTE_DIR"
    sshpass -e rsync -rltz --chmod=D755,F644 --dry-run --itemize-changes "${EXCLUDES[@]}" \
      -e "$SSH_CMD" "$ROOT_DIR/" "$SSH_USER@$SSH_HOST:$SSH_REMOTE_DIR/"
    ;;
  deploy)
    echo "🚀 Deploy → $SSH_USER@$SSH_HOST:$SSH_REMOTE_DIR"
    read -r -p "   Scrivi 'CONFERMO' per procedere: " ans
    [ "$ans" = "CONFERMO" ] || { echo "Annullato."; exit 1; }
    sshpass -e rsync -rltz --chmod=D755,F644 --itemize-changes "${EXCLUDES[@]}" \
      -e "$SSH_CMD" "$ROOT_DIR/" "$SSH_USER@$SSH_HOST:$SSH_REMOTE_DIR/"
    echo "✅ Deploy completato."
    ;;
  *)
    echo "Uso: ./deploy/ssh_sync.sh {preview|deploy}"; exit 1 ;;
esac
