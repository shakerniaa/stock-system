#!/usr/bin/env bash
# Builds the CSS/JS bundles and zips the theme for upload
# (Appearance → Themes → Add New → Upload Theme).
set -euo pipefail
cd "$(dirname "$0")/.."
( cd dev-tools && { [ -d node_modules ] || npm install --silent; } && node build-assets.mjs )
OUT="stocksystem-theme-$(date +%Y%m%d).zip"
rm -f "$OUT"
zip -qr "$OUT" stocksystem-theme -x '*.DS_Store' '*/Thumbs.db'
ls -lh "$OUT"
