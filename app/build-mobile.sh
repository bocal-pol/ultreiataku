#!/usr/bin/env bash
# Génère compostel-mobile.html : un SEUL fichier HTML autonome (CSS + JS + données inlinés)
# à copier tel quel sur le téléphone. Relancer après chaque modif de compostel.html.
set -euo pipefail
cd "$(dirname "$0")"

# Resynchronise les fiches (guides.js) depuis les .md sources avant d'assembler.
python build-guides.py || echo "(build-guides ignoré)"

SRC=compostel.html
OUT=compostel-mobile.html

{
  # 1) tête + favicon (lignes 1-8)
  sed -n '1,8p' "$SRC"
  # 2) Leaflet CSS inliné (remplace la ligne 9 : <link href="vendor/leaflet.css">)
  echo '<style>'
  cat vendor/leaflet.css
  echo '</style>'
  # 3) tout le reste de la tête + le corps (lignes 10-246, jusqu'avant les <script src>)
  sed -n '10,246p' "$SRC"
  # 4) les 5 dépendances inlinées (remplacent les lignes 247-251)
  for f in vendor/leaflet.js traces.js variants.js days.js guides.js; do
    echo '<script>'
    cat "$f"
    echo '</script>'
  done
  # 5) le script principal + fermeture (ligne 252 -> fin)
  sed -n '252,$p' "$SRC"
} > "$OUT"

echo "OK -> $OUT ($(wc -c < "$OUT") octets)"
