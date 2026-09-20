#!/usr/bin/env bash
# Rebuilds stocksystem-theme/assets/fonts/*.woff2 from the original TTFs in
# stocksystem-dev-kit/assets/fonts (Peyda). Needs: pip install fonttools brotli
set -euo pipefail
cd "$(dirname "$0")/.."
SRC=stocksystem-dev-kit/assets/fonts
OUT=stocksystem-theme/assets/fonts
UNICODES="U+0020-007E,U+00A0-00BF,U+00D7,U+00F7,U+0600-06FF,U+0750-077F,U+200B-200F,U+2010-2027,U+2030,U+2032-2033,U+2039-203A,U+2190-2193,U+2212,U+2022,U+2026,U+FB50-FDFF,U+FE70-FEFF,U+25CF,U+2713,U+2605"
for w in Regular Medium SemiBold Bold ExtraBold; do
  pyftsubset "$SRC/Peyda-$w.ttf" --unicodes="$UNICODES" --layout-features='*' \
    --flavor=woff2 --no-hinting --output-file="$OUT/Peyda-$w.woff2"
done
ls -l "$OUT"/*.woff2
