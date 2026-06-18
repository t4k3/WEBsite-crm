#!/usr/bin/env bash
# ==========================================
#  WAZLLEY - Sync FTP (locale <-> wazlley.takeoff.pro)
#  Uso:
#    ./deploy/sync.sh backup     # scarica il sito live in _live_backup/
#    ./deploy/sync.sh preview    # mostra cosa verrebbe caricato (NON tocca il server)
#    ./deploy/sync.sh deploy     # carica le modifiche locali -> server (chiede conferma)
# ==========================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
ENV_FILE="$SCRIPT_DIR/.env"

if [ ! -f "$ENV_FILE" ]; then
  echo "❌ Manca $ENV_FILE — copia deploy/.env.example in deploy/.env e compilalo."
  exit 1
fi
set -a; source "$ENV_FILE"; set +a
: "${FTP_HOST:?manca FTP_HOST in .env}"
: "${FTP_USER:?manca FTP_USER in .env}"
: "${FTP_PASS:?manca FTP_PASS in .env}"
REMOTE_DIR="${FTP_REMOTE_DIR:-/}"

# File/cartelle che NON vanno mai pubblicati sul server live
EXCLUDES=(
  --exclude-glob '.git/'
  --exclude-glob 'deploy/'
  --exclude-glob '_live_backup/'
  --exclude-glob '.gitignore'
  --exclude-glob '.DS_Store'
  --exclude-glob '*.zip'
  --exclude-glob '*.md'
  --exclude-glob 'QUICK_START.txt'
  --exclude-glob 'check_wazlley.php'   # script di self-test: non in produzione
)

run_lftp () {
  lftp -u "$FTP_USER,$FTP_PASS" "ftp://$FTP_HOST" <<EOF
set ftp:ssl-allow no
set net:max-retries 2
set net:timeout 15
$1
bye
EOF
}

cmd="${1:-}"
case "$cmd" in
  backup)
    echo "⬇️  Backup del sito live in $ROOT_DIR/_live_backup ..."
    mkdir -p "$ROOT_DIR/_live_backup"
    run_lftp "mirror --verbose \"$REMOTE_DIR\" \"$ROOT_DIR/_live_backup\""
    echo "✅ Backup completato."
    ;;
  preview)
    echo "🔍 ANTEPRIMA (dry-run) — locale -> $FTP_HOST:$REMOTE_DIR. Nessun file verrà modificato."
    run_lftp "mirror -R --dry-run --only-newer --verbose ${EXCLUDES[*]} \"$ROOT_DIR\" \"$REMOTE_DIR\""
    ;;
  deploy)
    echo "🚀 Stai per CARICARE le modifiche locali su $FTP_HOST:$REMOTE_DIR"
    read -r -p "   Scrivi 'CONFERMO' per procedere: " ans
    [ "$ans" = "CONFERMO" ] || { echo "Annullato."; exit 1; }
    run_lftp "mirror -R --only-newer --verbose ${EXCLUDES[*]} \"$ROOT_DIR\" \"$REMOTE_DIR\""
    echo "✅ Deploy completato."
    ;;
  *)
    echo "Uso: ./deploy/sync.sh {backup|preview|deploy}"
    exit 1
    ;;
esac
