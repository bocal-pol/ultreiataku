# Évolutions futures — Idées à développer

> Fichier vivant. Ajoute ici toute idée qui vient en cours de préparation ou de marche, pour les prochaines sessions.

## 🖥️ Site interactif de suivi du chemin (idée maîtresse)

**Vision** : une application web (ou app mobile) qui accompagne le pèlerin en temps réel pendant la marche.

**Fonctionnalités clés imaginées** :

### Suivi géolocalisé
- Carte interactive avec position en temps réel
- Trace du parcours accompli + trace à venir
- Distance parcourue / restante / du jour
- Estimation d'arrivée à la prochaine étape

### Prompts contextuels aux points-clés
Ce que tu décris : **à chaque approche d'un POI ou d'un détour, l'app propose une décision.**

Exemples concrets sur le tronçon Belgique :

```
⚡ Point de décision — J3, km 47

Tu approches de Sclayn. À droite, un détour de 1,5 km A/R
te mène à la Grotte Scladina, un des sites néandertaliens
les plus importants d'Europe.

⏱️  +45 min de marche + 1h30 de visite guidée
💰  ~8 €
⚠️  Réservation nécessaire (grotte@scladina.be)
     Aujourd'hui = premier dimanche du mois ? Visite à 14h possible.

  [→ Y aller]   [→ Continuer chemin normal]   [→ Reporter décision (rappeler dans 30 min)]
```

Ou plus loin :

```
⚡ Point de décision — J7, km 116

Tu es à Waulsort. Le passage d'eau manuel est ouvert
(dernier passeur de Wallonie encore en activité !).

⏱️  +20 min pour traversée + expérience unique
💰  Gratuit (service public)
🌤️  Météo : ensoleillé, idéal
✨  Rare et emblématique de la région

  [→ Prendre le bac]   [→ Rester sur RAVeL rive droite]
```

### Recommandations dynamiques
- **En fonction de la forme** (auto-évaluation matin : 1-10) → détours long/court/aucun
- **En fonction de la météo** → décision bivouac vs gîte
- **En fonction du budget restant** → filtrage des restaurants
- **En fonction de l'heure** → détour possible ou tard pour aujourd'hui

### Journal automatique
- Photos géolocalisées le long du trajet
- Notes vocales rapides
- Tampons credencial numérisés (photo)
- Météo enregistrée à chaque étape
- Souvenirs à partager (récap fin de journée)

### Partage famille
- Position en temps réel visible par proches (privacy contrôlée)
- Messages courts push (arrivé étape, bivouac installé, matinée en route)
- Photo du jour partagée automatiquement

### Data pour phases suivantes
- Retours d'expérience Belgique → alimentent Phase 2 France
- Comparaison poids réel vs estimé → ajustement matériel
- Blessures/moments critiques → base d'apprentissage

## 🛠️ Architecture technique envisageable

### Front-end
- **PWA** (Progressive Web App) — un site web qui s'installe comme une app, fonctionne offline
- Ou **app native Flutter / React Native** si besoin de fonctionnalités poussées (GPS background)

### Back-end
- **Base de données** : PostgreSQL + PostGIS pour la géo, ou SQLite embarqué pour offline-first
- **Cartes** : Mapbox (payant mais qualité) ou OpenStreetMap + Leaflet (gratuit)
- **Tracé GPX** : import Peregrinos + trace reconstitute + détours

### Décisionnel
- Système de règles simple : `si (proximité < 500 m ET POI actif ET météo OK ET heure < 17h) → afficher prompt`
- Interface d'admin pour ajouter des points de décision facilement

### Autonomie
- Fonctionnement offline complet (cartes + prompts pré-chargés par tronçon)
- Sync périodique quand connexion disponible

## 🎯 Priorisation MVP (minimum viable product)

**Version 1 — Tronçon Belgique seulement** :
- Import trace GPX
- Affichage carte + position temps réel
- 6 points de décision codés (les détours actuels)
- Journal photo simple

**Version 2 — Généralisation** :
- Extension France + Espagne
- Ajout météo temps réel
- Recommandations basées forme du jour
- Partage famille

**Version 3 — Communauté** :
- Autres pèlerins actifs sur le chemin (position anonymisée)
- Retour d'expérience partagé (état gîte, source d'eau, balisage)
- Statistiques agrégées (vitesse moyenne au km, dénivelé cumulé)

## 🚀 Prochaine session pour commencer

Quand tu voudras lancer le développement, tu peux invoquer :

```
/team-dev --full Application web PWA de suivi Chemin de Compostelle
avec points de décision contextuels aux POI et détours,
carte interactive avec trace GPX, mode offline complet,
partage famille temps réel.
```

Ça engagera l'équipe complète : `boussole-agent` (specs produit) → `vitruve-agent` (architecture technique) → `iris-agent` (design UX/UI) → `jem-agent` (React frontend) ou `nomade-agent` (Flutter mobile) → `bocal-agent` (backend API) → `argus-agent` (QA) → `cerbere-agent` (sécurité).

Le projet passerait par tous les groupes du team-dev en mode complet.

## 💡 Autres idées à explorer

- **Podcast quotidien** — enregistrer 5 min chaque soir avec ambiance sonore + réflexions
- **Blog en marche** — publication automatique fin de journée d'un résumé
- **Playlist musicale** dynamique selon paysage/moment (délégation Bard)
- **Journal papier** — un carnet Moleskine pour compléter le numérique (redondance sûre)
- **Groupe WhatsApp** de proches — communication asynchrone continue
- **Compostelle en photos NFT** — commémorer 1 image par étape sur blockchain personnelle (idée geek)
- **API publique** — permettre à d'autres pèlerins d'utiliser tes données de tracé
