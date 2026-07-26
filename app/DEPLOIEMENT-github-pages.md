# Déployer Ultreïataku en PWA sur GitHub Pages

Objectif : servir l'appli en **HTTPS** pour que le **GPS fonctionne dans le navigateur**, avec **cache hors-ligne** (installable via « Ajouter à l'écran d'accueil »).

Le dossier à publier est **`app/app-pwa/`** (généré par `build-pwa.sh`). Il contient : `index.html` (tout inliné), `sw.js`, `manifest.webmanifest`, `icon.svg`, `.nojekyll`.

> Terminal : **Git Bash**. Compte GitHub : **bocal-pol**.

## 1. S'authentifier (une seule fois)

```bash
gh auth login
# → GitHub.com → HTTPS → Login with a web browser → connecte le compte bocal-pol
```

## 2. Initialiser le dépôt du site (branche gh-pages, pas main)

```bash
cd /c/Projet/Compostel/app/app-pwa

git init -b gh-pages

# Identité du commit. Pour NE PAS exposer ton email pro publiquement,
# utilise ton email GitHub "noreply" (Settings > Emails > Keep my email private) :
git config user.name  "bocal-pol"
git config user.email "bocal-pol@users.noreply.github.com"   # adapte à ton vrai noreply

git add .
git commit -m "chore(pwa): publie ultreiataku hors-ligne"
```

## 3. Créer le dépôt distant public et pousser

```bash
gh repo create ultreiataku --public --source=. --remote=origin --push
```

## 4. Activer GitHub Pages sur la branche gh-pages

```bash
gh api -X POST repos/bocal-pol/ultreiataku/pages \
  -f "source[branch]=gh-pages" -f "source[path]=/"
```

Si la commande renvoie une erreur : va dans **GitHub → dépôt `ultreiataku` → Settings → Pages** et règle **Branch = `gh-pages`**, dossier **`/ (root)`**, puis **Save**.

## 5. Ouvrir sur le téléphone (après ~1 min de build)

URL : **https://bocal-pol.github.io/ultreiataku/**

1. Ouvre l'URL dans le navigateur du téléphone.
2. Menu du navigateur → **Ajouter à l'écran d'accueil**.
3. Ouvre l'icône Ultreïataku : le **GPS marche** (contexte HTTPS), et après une première ouverture avec réseau, l'appli et les tuiles consultées restent **hors-ligne**.

## Mettre à jour l'appli plus tard

Après chaque amélioration de `compostel.html` :

```bash
cd /c/Projet/Compostel/app
bash build-pwa.sh                 # régénère app-pwa/
cd app-pwa
git add .
git commit -m "feat(app): <ce qui a changé>"
git push
```

Le service worker sert une version en cache : pour forcer la mise à jour sur le téléphone, rouvre l'appli deux fois (le SW récupère la nouvelle version en arrière-plan), ou vide le cache du site.

## Confidentialité

Le dépôt est **public** : ton itinéraire est techniquement accessible via l'URL. Aucun secret n'est présent dans les fichiers. Si tu préfères le privé, GitHub Pages sur dépôt privé nécessite un compte payant — sinon on bascule sur Outdooractive pour le suivi GPS.
