# Changelog — Ultreiataku

Toutes les modifications notables de ce projet sont documentées dans ce fichier.

Format : [Keep a Changelog](https://keepachangelog.com/fr/1.1.0/)
Versionnement : [Semantic Versioning](https://semver.org/)

---

## [1.0.0] — 2026-07-28

### Added

#### Vague 1a — Chemin (parcours, carte, navigation hors ligne)

- Consultation des 12 etapes de la Via Mosana (Liege-Santiago, troncon belge) et des 12 etapes de la Via Monastique, avec distance, denivele positif, duree estimee et type d'hebergement du soir.
- Fiche detail par etape : resume, points d'interet types avec icons, points d'eau, note pratique du terrain.
- Carte interactive Leaflet avec trace GPX de l'etape (bleu), detours optionnels (pointille orange) et marqueurs des points d'interet cliquables.
- Mode hors ligne complet : tiles OSM pre-telechargees (zoom 12-16, emprise Belgique), donnees JSON des etapes et traces GPX disponibles sans connexion apres premier chargement.
- Service Worker avec strategies Cache First (tiles, GeoJSON), Stale While Revalidate (donnees etapes) et IndexedDB pour la persistence locale.

#### Vague 1b — Vivre (hebergements et ravitaillement)

- Section hebergement dans chaque fiche etape : hebergement principal (nom, adresse, contact, tarif, equipements, tampon credencial), alternatives pliables, indication bivouac legal si applicable.
- Section repas : repas par moment de la journee (petit-dejeuner, midi, soir), mise en avant de la specialite locale, ration journaliere de reference (3 200 kcal / 750 g), lieux de ravitaillement.
- Administration Filament : gestion des hebergements et des repas par etape, widget de suivi des hebergements dont la verification remonte a plus de 6 mois.

#### Vague 1c — Pelerins (voyages partages et invitations)

- Creation d'un voyage (Trip) nomme, associe a une route et une date estimee.
- Invitation d'autres pelerins via un lien a usage unique (token UUID v4), avec email d'invitation en francais, neerlandais et allemand.
- Tableau de bord du voyage : liste des membres avec leurs roles (organisateur / participant / observateur), progression en jours, prochaine etape.
- Planification de departs par etape et affichage de l'occupation prevue dans les fiches hebergement ("2 pelerins prevus cette nuit").
- Droits stricts par role : l'organisateur administre le voyage, le participant planifie ses propres departs, l'observateur consulte les informations publiques.

#### Vague 1d — Sac (gestion du poids)

- Gestion de scenarios de sac (Solo 8,5 kg / Duo 7,5 kg par personne) avec liste d'items repartis par categorie (bivouac, habillement, hygiene, securite, etc.).
- Ecran "Mon Sac" avec jauge visuelle de poids (vert / orange / rouge selon l'objectif cible), sections expandables par categorie et total base weight separe du poids eau et nourriture.
- Assignation des items par etape en voyage en duo : qui porte quoi pour chaque journee.
- Seeds integres : 35 items peses reels (Osprey Exos, MSR Hubba Hubba, NeoAir, Sawyer Squeeze...) issus du projet source.

#### Vague 1e — Carnet de voyage (journal hors ligne)

- Redaction d'entrees de journal par etape : titre, texte libre, humeur, kilometres du jour, visibilite (prive / membres du voyage / public).
- Creation d'entrees hors ligne : sauvegarde automatique dans IndexedDB, synchronisation au retour de la connexion (Background Sync API avec fallback polling 60s pour iOS).
- Upload de photos (galerie ou appareil photo) avec suppression automatique des metadonnees EXIF incluant les coordonnees GPS, sauf consentement explicite de l'utilisateur.
- Affichage des photos via proxy authentifie (jamais d'URL MinIO directe), cache Service Worker 24h.
- Controle de visibilite filtre au niveau SQL : un observateur ne voit que les entrees publiques, un membre voit les entrees "membres" et publiques, l'auteur voit toutes les siennes.

#### Infrastructure et transverses

- Stack full-stack Laravel 13.21 + Filament 4.12 (backend) et React 19 + Vite 8 (PWA frontend).
- Authentification SSO unifiee avec l'Auth central SiteV26 : connexion via Passport OAuth2, creation automatique du profil pelerin au premier login.
- Interface d'administration Filament avec acces reserve aux roles admin et super-admin via le SSO.
- Internationalisation complete : francais, neerlandais et allemand sur toutes les surfaces utilisateur (frontend et emails).
- CI GitHub Actions : lint (Pint + ESLint/oxlint), PHPUnit, Playwright, PHPStan niveau 6.
- Stockage GPX et photos journal sur MinIO (disks dedies `minio_gpx` et `minio_journal`), jamais de fichier public direct.
- Droits RGPD : export du profil et de l'historique (`GET /api/pilgrimage/me/export`) et suppression de compte avec purge asynchrone des assets MinIO (`DELETE /api/pilgrimage/me`).

### Security

- **P0-01 corrige** : guard API reconfigure en mode cookie de session (Sanctum stateful SPA) ; les endpoints protegds ne s'appuient plus sur un driver session ambigu.
- **P0-02 corrige** : cles `services.auth.*` ajoutees dans `config/services.php` ; le SSO Filament ne leve plus de `TransportException` systematique.
- **P1-01 corrige** : contournement CSRF OAuth corrige dans `SsoCallbackController` (condition `&&` stricte ; un state vide est rejete).
- **P1-02 corrige** : IDOR departure corrige ; un participant ne peut creer un depart qu'en son nom propre.
- **P1-03 corrige** : token Bearer SSO supprime de la session Redis ; seuls les identifiants necessaires a l'UX Filament sont persistes.
- **P1-04 corrige** : l'upload de photo echoue explicitement si GD ne peut pas traiter le fichier (plus de fallback silencieux laissant transiter les EXIF).
- **P2-03 corrige** : `LIBXML_NONET` ajoute dans `GpxXmlParser` pour bloquer tout fetch DTD externe.
- **Seeder GPX corrige** : `GpxImportService::doImport` ne masque plus les exceptions MinIO ; le seeder cree correctement un trace de fallback en cas d'echec d'upload.

### Fixed

- **BUG-ULTREIA-P0-001** : `REDIS_PASSWORD=null` dans `.env` Ultreiataku corrige (`password` litteral) ; tous les endpoints ne retournent plus HTTP 500 par defaut.
- **BUG-ULTREIA-P0-002** : `DB_HOST=sql-pgsql` corrige en `site-pgsql` ; le container ne redemarrait plus en boucle.
- **BUG-ULTREIA-P1-001** : tri des etapes corrige ; la Via Mosana et la Via Monastique s'affichent separement, ordonnees par `route_id, sort_order` et non par `sort_order` global partage.
- **BUG-ULTREIA-P1-002** : affichage du nom du repas corrige pour les specialites locales ; `meal.name` s'affiche en titre, `restaurantName` en sous-titre.
- Corrections de la revue Themis I-05/I-06 : filtre "Mes entrees" corrige dans `useJournalEntries` (filtrage cote client sur `pilgrimId` courant) ; fallback copier-lien remplace `execCommand` deprecie par un champ texte selectionnable.
- `expose user_id` dans `PilgrimResource` pour satisfaire le contrat frontend SSO.
- Regressions GPX et administration Filament corrigees apres livraison vague 1c.

---

## [0.1.0] — 2026-07-24

### Added

- Migration de la PWA HTML historique vers `legacy/` ; scaffolding Laravel 13 + React 19 + Docker (volumes code + vendor, Vite usePolling, `compose.yaml` aligne sur le cluster site-pgsql).

[1.0.0]: https://github.com/bocal-pol/SiteV26/compare/v0.1.0...v1.0.0
[0.1.0]: https://github.com/bocal-pol/SiteV26/releases/tag/v0.1.0
