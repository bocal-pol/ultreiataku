#!/usr/bin/env bash
# Génère le dossier app-pwa/ : appli web installable (PWA) à déployer sur un hébergeur HTTPS.
# Contenu prêt à mettre en ligne : index.html (autonome) + sw.js + manifest + icon.
# Relancer après chaque modif de compostel.html.
set -euo pipefail
cd "$(dirname "$0")"

bash build-mobile.sh          # (re)génère compostel-mobile.html (tout inliné)

mkdir -p app-pwa

# index.html = fichier autonome + injection du lien manifeste (après </title>)
# et de l'enregistrement du service worker (avant </body>).
awk '
  /<\/title>/ && !m {
    print;
    print "<link rel=\"manifest\" href=\"manifest.webmanifest\">";
    print "<meta name=\"apple-mobile-web-app-capable\" content=\"yes\">";
    print "<meta name=\"apple-mobile-web-app-status-bar-style\" content=\"black-translucent\">";
    print "<meta name=\"apple-mobile-web-app-title\" content=\"Ultreïataku\">";
    print "<link rel=\"apple-touch-icon\" href=\"icon.svg\">";
    m=1; next
  }
  /<\/body>/ && !s {
    print "<script>if(\"serviceWorker\" in navigator){window.addEventListener(\"load\",function(){navigator.serviceWorker.register(\"sw.js\").catch(function(){});});}</script>";
    print; s=1; next
  }
  { print }
' compostel-mobile.html > app-pwa/index.html

cp manifest.webmanifest sw.js icon.svg app-pwa/
touch app-pwa/.nojekyll

echo "OK -> app-pwa/ :"
ls -la app-pwa/
