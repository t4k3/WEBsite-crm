#!/usr/bin/env bash
# photo2webp.sh <input> <nome-output> [larghezza]
# Rimuove lo sfondo (ritaglio stretto) e salva assets/img/<nome-output>.webp trasparente.
# Es: ./tools/photo2webp.sh images/IMG_4999.jpeg ballgun_speed_test 1000
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
IN="$1"; NAME="$2"; W="${3:-1000}"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
swift "$SCRIPT_DIR/removebg.swift" "$IN" "$TMP/cut.png" --crop
cwebp -quiet -q 82 -resize "$W" 0 "$TMP/cut.png" -o "$ROOT/assets/img/$NAME.webp"
echo "-> assets/img/$NAME.webp ($(sips -g pixelWidth -g pixelHeight "$ROOT/assets/img/$NAME.webp" 2>/dev/null | grep pixel | tr '\n' ' '))"
