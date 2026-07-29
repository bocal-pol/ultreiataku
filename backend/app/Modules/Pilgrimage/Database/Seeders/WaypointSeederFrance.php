<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Waypoint;
use Illuminate\Database\Seeder;

/**
 * Waypoints France : villes-étapes + POI vérifiés (patrimoine-france.md).
 * Coordonnées réelles issues des sources cartographiques.
 *
 * Sources : poi/patrimoine-france.md · etapes/etapes-france.md
 */
class WaypointSeederFrance extends Seeder
{
    public function run(): void
    {
        $waypoints = [
            // ── Villes-étapes (city) ─────────────────────────────────────────

            ['slug' => 'rocroi-ville', 'name' => ['fr' => 'Rocroi', 'nl' => 'Rocroi', 'de' => 'Rocroi'], 'type' => 'city', 'poi_category' => null, 'latitude' => 49.9267, 'longitude' => 4.5253, 'is_active' => true],
            ['slug' => 'signy-labbaye', 'name' => ['fr' => 'Signy-l\'Abbaye', 'nl' => 'Signy-l\'Abbaye', 'de' => 'Signy-l\'Abbaye'], 'type' => 'city', 'poi_category' => null, 'latitude' => 49.6983, 'longitude' => 4.4178, 'is_active' => true],
            ['slug' => 'chateau-porcien', 'name' => ['fr' => 'Château-Porcien', 'nl' => 'Château-Porcien', 'de' => 'Château-Porcien'], 'type' => 'city', 'poi_category' => null, 'latitude' => 49.5283, 'longitude' => 4.2478, 'is_active' => true],
            ['slug' => 'reims-cathedrale', 'name' => ['fr' => 'Reims — Cathédrale Notre-Dame', 'nl' => 'Reims — Kathedraal Notre-Dame', 'de' => 'Reims — Kathedrale Notre-Dame'], 'type' => 'city', 'poi_category' => null, 'latitude' => 49.2533, 'longitude' => 4.0342, 'is_active' => true],
            ['slug' => 'verzy', 'name' => ['fr' => 'Verzy', 'nl' => 'Verzy', 'de' => 'Verzy'], 'type' => 'city', 'poi_category' => null, 'latitude' => 49.1547, 'longitude' => 4.1553, 'is_active' => true],
            ['slug' => 'chalons-en-champagne', 'name' => ['fr' => 'Châlons-en-Champagne', 'nl' => 'Châlons-en-Champagne', 'de' => 'Châlons-en-Champagne'], 'type' => 'city', 'poi_category' => null, 'latitude' => 48.9575, 'longitude' => 4.3639, 'is_active' => true],
            ['slug' => 'vitry-le-francois', 'name' => ['fr' => 'Vitry-le-François', 'nl' => 'Vitry-le-François', 'de' => 'Vitry-le-François'], 'type' => 'city', 'poi_category' => null, 'latitude' => 48.7247, 'longitude' => 4.5842, 'is_active' => true],
            ['slug' => 'giffaumont-champaubert', 'name' => ['fr' => 'Giffaumont-Champaubert (Lac du Der)', 'nl' => 'Giffaumont-Champaubert (Lac du Der)', 'de' => 'Giffaumont-Champaubert (Lac du Der)'], 'type' => 'city', 'poi_category' => null, 'latitude' => 48.5742, 'longitude' => 4.7625, 'is_active' => true],
            ['slug' => 'montier-en-der', 'name' => ['fr' => 'Montier-en-Der', 'nl' => 'Montier-en-Der', 'de' => 'Montier-en-Der'], 'type' => 'city', 'poi_category' => null, 'latitude' => 48.4758, 'longitude' => 4.7594, 'is_active' => true],
            ['slug' => 'bar-sur-seine', 'name' => ['fr' => 'Bar-sur-Seine', 'nl' => 'Bar-sur-Seine', 'de' => 'Bar-sur-Seine'], 'type' => 'city', 'poi_category' => null, 'latitude' => 48.1083, 'longitude' => 4.3736, 'is_active' => true],
            ['slug' => 'les-riceys', 'name' => ['fr' => 'Les Riceys', 'nl' => 'Les Riceys', 'de' => 'Les Riceys'], 'type' => 'city', 'poi_category' => null, 'latitude' => 47.9939, 'longitude' => 4.3617, 'is_active' => true],
            ['slug' => 'tonnerre', 'name' => ['fr' => 'Tonnerre', 'nl' => 'Tonnerre', 'de' => 'Tonnerre'], 'type' => 'city', 'poi_category' => null, 'latitude' => 47.8583, 'longitude' => 3.9772, 'is_active' => true],
            ['slug' => 'chablis', 'name' => ['fr' => 'Chablis', 'nl' => 'Chablis', 'de' => 'Chablis'], 'type' => 'city', 'poi_category' => null, 'latitude' => 47.8150, 'longitude' => 3.8006, 'is_active' => true],
            ['slug' => 'auxerre', 'name' => ['fr' => 'Auxerre', 'nl' => 'Auxerre', 'de' => 'Auxerre'], 'type' => 'city', 'poi_category' => null, 'latitude' => 47.7980, 'longitude' => 3.5697, 'is_active' => true],
            ['slug' => 'arcy-sur-cure', 'name' => ['fr' => 'Arcy-sur-Cure', 'nl' => 'Arcy-sur-Cure', 'de' => 'Arcy-sur-Cure'], 'type' => 'city', 'poi_category' => null, 'latitude' => 47.5867, 'longitude' => 3.7606, 'is_active' => true],
            ['slug' => 'vezelay', 'name' => ['fr' => 'Vézelay', 'nl' => 'Vézelay', 'de' => 'Vézelay'], 'type' => 'city', 'poi_category' => null, 'latitude' => 47.4658, 'longitude' => 3.7447, 'is_active' => true],
            ['slug' => 'clamecy', 'name' => ['fr' => 'Clamecy', 'nl' => 'Clamecy', 'de' => 'Clamecy'], 'type' => 'city', 'poi_category' => null, 'latitude' => 47.4611, 'longitude' => 3.5183, 'is_active' => true],
            ['slug' => 'la-charite-sur-loire', 'name' => ['fr' => 'La Charité-sur-Loire', 'nl' => 'La Charité-sur-Loire', 'de' => 'La Charité-sur-Loire'], 'type' => 'city', 'poi_category' => null, 'latitude' => 47.1789, 'longitude' => 3.0156, 'is_active' => true],
            ['slug' => 'bourges', 'name' => ['fr' => 'Bourges', 'nl' => 'Bourges', 'de' => 'Bourges'], 'type' => 'city', 'poi_category' => null, 'latitude' => 47.0810, 'longitude' => 2.3966, 'is_active' => true],
            ['slug' => 'issoudun', 'name' => ['fr' => 'Issoudun', 'nl' => 'Issoudun', 'de' => 'Issoudun'], 'type' => 'city', 'poi_category' => null, 'latitude' => 46.9483, 'longitude' => 1.9944, 'is_active' => true],
            ['slug' => 'chateauroux', 'name' => ['fr' => 'Châteauroux', 'nl' => 'Châteauroux', 'de' => 'Châteauroux'], 'type' => 'city', 'poi_category' => null, 'latitude' => 46.8117, 'longitude' => 1.6913, 'is_active' => true],
            ['slug' => 'argenton-sur-creuse', 'name' => ['fr' => 'Argenton-sur-Creuse', 'nl' => 'Argenton-sur-Creuse', 'de' => 'Argenton-sur-Creuse'], 'type' => 'city', 'poi_category' => null, 'latitude' => 46.5878, 'longitude' => 1.5136, 'is_active' => true],
            ['slug' => 'la-souterraine', 'name' => ['fr' => 'La Souterraine', 'nl' => 'La Souterraine', 'de' => 'La Souterraine'], 'type' => 'city', 'poi_category' => null, 'latitude' => 46.2342, 'longitude' => 1.4872, 'is_active' => true],
            ['slug' => 'le-dorat', 'name' => ['fr' => 'Le Dorat', 'nl' => 'Le Dorat', 'de' => 'Le Dorat'], 'type' => 'city', 'poi_category' => null, 'latitude' => 46.2244, 'longitude' => 1.0786, 'is_active' => true],
            ['slug' => 'bellac', 'name' => ['fr' => 'Bellac', 'nl' => 'Bellac', 'de' => 'Bellac'], 'type' => 'city', 'poi_category' => null, 'latitude' => 46.1211, 'longitude' => 1.0539, 'is_active' => true],
            ['slug' => 'oradour-sur-glane', 'name' => ['fr' => 'Oradour-sur-Glane', 'nl' => 'Oradour-sur-Glane', 'de' => 'Oradour-sur-Glane'], 'type' => 'city', 'poi_category' => null, 'latitude' => 45.9322, 'longitude' => 1.0336, 'is_active' => true],
            ['slug' => 'limoges', 'name' => ['fr' => 'Limoges', 'nl' => 'Limoges', 'de' => 'Limoges'], 'type' => 'city', 'poi_category' => null, 'latitude' => 45.8315, 'longitude' => 1.2578, 'is_active' => true],
            ['slug' => 'chalus', 'name' => ['fr' => 'Châlus', 'nl' => 'Châlus', 'de' => 'Châlus'], 'type' => 'city', 'poi_category' => null, 'latitude' => 45.6558, 'longitude' => 0.9786, 'is_active' => true],
            ['slug' => 'thiviers', 'name' => ['fr' => 'Thiviers', 'nl' => 'Thiviers', 'de' => 'Thiviers'], 'type' => 'city', 'poi_category' => null, 'latitude' => 45.4161, 'longitude' => 0.9108, 'is_active' => true],
            ['slug' => 'sorges', 'name' => ['fr' => 'Sorges', 'nl' => 'Sorges', 'de' => 'Sorges'], 'type' => 'city', 'poi_category' => null, 'latitude' => 45.2936, 'longitude' => 0.8747, 'is_active' => true],
            ['slug' => 'perigueux', 'name' => ['fr' => 'Périgueux', 'nl' => 'Périgueux', 'de' => 'Périgueux'], 'type' => 'city', 'poi_category' => null, 'latitude' => 45.1852, 'longitude' => 0.7203, 'is_active' => true],
            ['slug' => 'sainte-foy-la-grande', 'name' => ['fr' => 'Sainte-Foy-la-Grande', 'nl' => 'Sainte-Foy-la-Grande', 'de' => 'Sainte-Foy-la-Grande'], 'type' => 'city', 'poi_category' => null, 'latitude' => 44.8389, 'longitude' => 0.2186, 'is_active' => true],
            ['slug' => 'la-reole', 'name' => ['fr' => 'La Réole', 'nl' => 'La Réole', 'de' => 'La Réole'], 'type' => 'city', 'poi_category' => null, 'latitude' => 44.5894, 'longitude' => -0.0322, 'is_active' => true],
            ['slug' => 'bazas', 'name' => ['fr' => 'Bazas', 'nl' => 'Bazas', 'de' => 'Bazas'], 'type' => 'city', 'poi_category' => null, 'latitude' => 44.4322, 'longitude' => -0.2133, 'is_active' => true],
            ['slug' => 'mont-de-marsan', 'name' => ['fr' => 'Mont-de-Marsan', 'nl' => 'Mont-de-Marsan', 'de' => 'Mont-de-Marsan'], 'type' => 'city', 'poi_category' => null, 'latitude' => 43.8939, 'longitude' => -0.5006, 'is_active' => true],
            ['slug' => 'saint-sever', 'name' => ['fr' => 'Saint-Sever', 'nl' => 'Saint-Sever', 'de' => 'Saint-Sever'], 'type' => 'city', 'poi_category' => null, 'latitude' => 43.7597, 'longitude' => -0.5728, 'is_active' => true],
            ['slug' => 'orthez', 'name' => ['fr' => 'Orthez', 'nl' => 'Orthez', 'de' => 'Orthez'], 'type' => 'city', 'poi_category' => null, 'latitude' => 43.4883, 'longitude' => -0.7719, 'is_active' => true],
            ['slug' => 'sauveterre-de-bearn', 'name' => ['fr' => 'Sauveterre-de-Béarn', 'nl' => 'Sauveterre-de-Béarn', 'de' => 'Sauveterre-de-Béarn'], 'type' => 'city', 'poi_category' => null, 'latitude' => 43.3981, 'longitude' => -0.9444, 'is_active' => true],
            ['slug' => 'saint-palais', 'name' => ['fr' => 'Saint-Palais', 'nl' => 'Saint-Palais', 'de' => 'Saint-Palais'], 'type' => 'city', 'poi_category' => null, 'latitude' => 43.3256, 'longitude' => -1.0344, 'is_active' => true],
            ['slug' => 'ostabat-asme', 'name' => ['fr' => 'Ostabat-Asme', 'nl' => 'Ostabat-Asme', 'de' => 'Ostabat-Asme'], 'type' => 'city', 'poi_category' => null, 'latitude' => 43.3197, 'longitude' => -1.1133, 'is_active' => true],
            ['slug' => 'saint-jean-pied-de-port', 'name' => ['fr' => 'Saint-Jean-Pied-de-Port', 'nl' => 'Saint-Jean-Pied-de-Port', 'de' => 'Saint-Jean-Pied-de-Port'], 'type' => 'city', 'poi_category' => null, 'latitude' => 43.1636, 'longitude' => -1.2367, 'is_active' => true],

            // ── POI remarquables France ──────────────────────────────────────

            [
                'slug' => 'cathedrale-reims',
                'name' => ['fr' => 'Cathédrale Notre-Dame de Reims (UNESCO)', 'nl' => 'Kathedraal Notre-Dame van Reims (UNESCO)', 'de' => 'Kathedrale Notre-Dame von Reims (UNESCO)'],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 49.2533,
                'longitude' => 4.0342,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 60,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Cathédrale du sacre des rois de France (UNESCO). L\'Ange au Sourire, vitraux de Marc Chagall, tympan du Jugement Dernier. 25 rois de France y furent sacrés. Tampon credencial à la sacristie.',
                    'nl' => 'Krooningskathedraal der Franse koningen (UNESCO). De Lachende Engel, glas-in-loodramen van Marc Chagall. 25 Franse koningen werden hier gekroond. Pelgrimsstempel in de sacristie.',
                    'de' => 'Krönungskathedrale der französischen Könige (UNESCO). Der Lächelnde Engel, Marc-Chagall-Glasfenster. 25 französische Könige wurden hier gekrönt. Pilgerstempel in der Sakristei.',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'faux-de-verzy',
                'name' => ['fr' => 'Les Faux de Verzy — hêtres tortillards', 'nl' => 'De Faux de Verzy — kronkelbeuken', 'de' => 'Faux de Verzy — Tortillard-Buchen'],
                'type' => 'poi',
                'poi_category' => 'nature',
                'latitude' => 49.1422,
                'longitude' => 4.1667,
                'detour_type' => 'short',
                'detour_distance_km' => 1.50,
                'detour_duration_min' => 20,
                'visit_duration_min' => 90,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Plus grande réserve mondiale de hêtres tortillards (~1 000 arbres mutants aux branches en parapluie, certains de 300+ ans). Réserve biologique ONF sur le plateau de la Montagne de Reims. Boucle balisée 4 km sur caillebotis. Gratuit, libre. Magique au lever du jour (plan conseillé : nuit à Verzy + boucle au matin J8).',
                    'nl' => 'Grootste wereldreserve van kronkelbeuken (~1 000 gemuteerde bomen met parapluvormige takken, sommige 300+ jaar oud). ONF biologisch reservaat op het plateau van de Montagne de Reims. Gemarkeerde lus 4 km. Gratis, vrij toegankelijk. Magisch bij zonsopgang.',
                    'de' => 'Größtes Weltvorkommen der Tortillard-Buchen (~1 000 mutierte Bäume mit schirmförmigen Ästen, einige 300+ Jahre alt). ONF-Naturschutzgebiet auf dem Plateau der Montagne de Reims. Markierte Rundtour 4 km. Kostenlos, frei zugänglich. Magisch im Morgengrauen.',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'basilique-vezelay',
                'name' => ['fr' => 'Basilique Sainte-Marie-Madeleine de Vézelay (UNESCO)', 'nl' => 'Basiliek van de Heilige Maria-Magdalena van Vézelay (UNESCO)', 'de' => 'Basilika Sainte-Marie-Madeleine von Vézelay (UNESCO)'],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 47.4658,
                'longitude' => 3.7447,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 90,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Basilique UNESCO, tympan roman du Christ en gloire, alignement de lumière au solstice d\'été (21 juin), crypte avec reliques de Marie-Madeleine. Point de départ historique de la Via Lemovicensis vers Compostelle. Credencial n°2 à retirer ici.',
                    'nl' => 'UNESCO-basiliek, romaans tympaan van Christus in glorie, lichtval bij zomerzonnestilstand (21 juni), crypte met relikwieën van Maria Magdalena. Historisch vertrekpunt van de Via Lemovicensis naar Compostela. Credencial nr. 2 hier afhalen.',
                    'de' => 'UNESCO-Basilika, romanisches Tympanon des Christus in Herrlichkeit, Lichtausrichtung zur Sommersonnenwende (21. Juni), Krypta mit Reliquien der Maria Magdalena. Historischer Ausgangspunkt der Via Lemovicensis. Credencial Nr. 2 hier abholen.',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'prieures-la-charite-loire',
                'name' => ['fr' => 'Prieuré clunisien Notre-Dame, La Charité-sur-Loire (UNESCO)', 'nl' => 'Cluniacenzerpriory Onze-Lieve-Vrouw, La Charité-sur-Loire (UNESCO)', 'de' => 'Cluniazenserpriorat Notre-Dame, La Charité-sur-Loire (UNESCO)'],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 47.1789,
                'longitude' => 3.0156,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 45,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => '"Fille aînée de Cluny" — prieuré clunisien UNESCO, ville du livre. Traversée de la Loire au pont de pierre. Accueil pèlerin donativo sur place.',
                    'nl' => '"Oudste dochter van Cluny" — cluniacenzerpriorij UNESCO, stad van het boek. Oversteek van de Loire via de stenen brug. Pelgrimsopvang donativo.',
                    'de' => '"Älteste Tochter von Cluny" — Cluniazenserpriorat UNESCO, Stadt des Buches. Überquerung der Loire auf der Steinbrücke. Pilgeraufnahme Donativo.',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'cathedrale-bourges',
                'name' => ['fr' => 'Cathédrale Saint-Étienne de Bourges (UNESCO)', 'nl' => 'Kathedraal Saint-Étienne van Bourges (UNESCO)', 'de' => 'Kathedrale Saint-Étienne von Bourges (UNESCO)'],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 47.0835,
                'longitude' => 2.3981,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 60,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'La plus large nef gothique de France. Vitraux XIIIe siècle exceptionnels. Palais Jacques-Cœur à 400 m (gothique civil flamboyant). Les forestines de Maison Forestines depuis 1879.',
                    'nl' => 'Het breedste gotische schip van Frankrijk. Uitzonderlijke 13e-eeuwse glas-in-loodramen. Palais Jacques-Cœur op 400 m. De forestines van Maison Forestines (1879).',
                    'de' => 'Das breiteste gotische Schiff Frankreichs. Außergewöhnliche Glasfenster aus dem 13. Jh. Palais Jacques-Cœur 400 m entfernt. Die Forestines der Maison Forestines (1879).',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'village-martyr-oradour',
                'name' => ['fr' => 'Village martyr d\'Oradour-sur-Glane', 'nl' => 'Martelaarsdorp Oradour-sur-Glane', 'de' => 'Märtyrerdorf Oradour-sur-Glane'],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 45.9322,
                'longitude' => 1.0336,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 150,
                'entry_cost_eur' => 8.00,
                'booking_required' => false,
                'booking_contact' => 'https://www.oradour.org/',
                'opening_notes' => [
                    'fr' => 'Village ruines : gratuit et ouvert toute l\'année. Centre de la Mémoire : ~8 €, exposition. Prévoir 2-3 h. Silence demandé.',
                    'nl' => 'Ruïnesdorp: gratis en het hele jaar open. Geheugencentrum: ~8 €, tentoonstelling. 2-3 uur voorzien. Stilte vereist.',
                    'de' => 'Ruinendorf: kostenlos, ganzjährig geöffnet. Gedenkzentrum: ~8 €, Ausstellung. 2-3 Stunden einplanen. Stille erbeten.',
                ],
                'description' => [
                    'fr' => '642 habitants massacrés le 10 juin 1944 par la division SS Das Reich. Ruines conservées en l\'état sur ordre du général de Gaulle. Voitures calcinées, machines à coudre, rails du tramway figés dans le temps. Intégré au tracé via la variante ouest GR654 — aucun détour. Étape volontairement calée avant le repos de Limoges.',
                    'nl' => '642 inwoners vermoord op 10 juni 1944 door de SS-divisie Das Reich. Ruïnes bewaard op bevel van generaal de Gaulle. Verbrande auto\'s, naaimachines, tramrails bevroren in de tijd. Geïntegreerd in het tracé via de westelijke variant GR654 — geen omweg.',
                    'de' => '642 Einwohner am 10. Juni 1944 von der SS-Division Das Reich massakriert. Ruinen auf Befehl von General de Gaulle erhalten. Verkohlte Autos, Nähmaschinen, Straßenbahnschienen in der Zeit eingefroren. In die Route via Westvariante GR654 integriert — kein Umweg.',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'cathedrale-saint-front-perigueux',
                'name' => ['fr' => 'Cathédrale Saint-Front de Périgueux (UNESCO)', 'nl' => 'Kathedraal Saint-Front van Périgueux (UNESCO)', 'de' => 'Kathedrale Saint-Front von Périgueux (UNESCO)'],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 45.1847,
                'longitude' => 0.7194,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 45,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Cathédrale à coupoles byzantines unique en France (UNESCO chemins de Saint-Jacques). L\'Eschif de Creyssac à 100 m : loge de guet à colombages de 1347, classée Monument Historique, meilleur point photo des quais de l\'Isle.',
                    'nl' => 'Kathedraal met Byzantijnse koepels, uniek in Frankrijk (UNESCO). De Eschif de Creyssac op 100 m: wachthuis met vakwerk uit 1347, Monument Historique, mooiste fotopunt aan de Isle.',
                    'de' => 'Kathedrale mit byzantinischen Kuppeln, in Frankreich einzigartig (UNESCO). Eschif de Creyssac 100 m entfernt: Wachhaus mit Fachwerk von 1347, Historisches Denkmal, schönstes Fotomotiv an der Isle.',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'cathedrale-bazas',
                'name' => ['fr' => 'Cathédrale Saint-Jean-Baptiste de Bazas (UNESCO)', 'nl' => 'Kathedraal Sint-Jan de Doper van Bazas (UNESCO)', 'de' => 'Kathedrale Saint-Jean-Baptiste von Bazas (UNESCO)'],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 44.4322,
                'longitude' => -0.2133,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 30,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Cathédrale UNESCO chemins de Saint-Jacques. Place à arcades médiévale. Bœuf de Bazas AOC à la table locale (entrecôte grillée aux sarments).',
                    'nl' => 'UNESCO-kathedraal Jacobswegen. Middeleeuws plein met arcades. Boeuf de Bazas AOC in het lokale restaurant (gegrilde entrecote op wijnranken).',
                    'de' => 'UNESCO-Kathedrale Jakobswege. Mittelalterlicher Arkadensaal. Boeuf de Bazas AOC im lokalen Restaurant (gegrilltes Entrecôte auf Weinreben).',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'abbatiale-saint-sever',
                'name' => ['fr' => 'Abbatiale de Saint-Sever (UNESCO)', 'nl' => 'Abdijkerk van Saint-Sever (UNESCO)', 'de' => 'Abteikirche Saint-Sever (UNESCO)'],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 43.7597,
                'longitude' => -0.5728,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 30,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Abbatiale UNESCO chemins de Saint-Jacques. Chapiteaux romans polychromes remarquables. Départ vers la Chalosse et le Béarn.',
                    'nl' => 'UNESCO-abdijkerk Jacobswegen. Opmerkelijke polychrome romaanse kapiteelbeeldhouwwerken.',
                    'de' => 'UNESCO-Abteikirche Jakobswege. Bemerkenswerte polychrome romanische Kapitelle.',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'stele-gibraltar',
                'name' => ['fr' => 'Stèle de Gibraltar — convergence des trois voies', 'nl' => 'Stèle van Gibraltar — convergentie der drie wegen', 'de' => 'Stele von Gibraltar — Zusammentreffen der drei Wege'],
                'type' => 'poi',
                'poi_category' => 'religious',
                'latitude' => 43.3542,
                'longitude' => -1.0828,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 15,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Lieu symbolique où convergent les trois voies françaises de Compostelle (Tours, Vézelay, Le Puy). À partir d\'ici, tous les pèlerins de France marchent ensemble sur le même chemin vers SJPP.',
                    'nl' => 'Symbolische plek waar de drie Franse pelgrimswegen naar Compostela samenkomen (Tours, Vézelay, Le Puy). Vanaf hier lopen alle Franse pelgrims samen op hetzelfde pad naar SJPP.',
                    'de' => 'Symbolischer Ort, wo die drei französischen Jakobswege zusammentreffen (Tours, Vézelay, Le Puy). Von hier aus gehen alle französischen Pilger gemeinsam auf demselben Weg nach SJPP.',
                ],
                'is_active' => true,
            ],
            [
                'slug' => 'porte-saint-jacques-sjpp',
                'name' => ['fr' => 'Porte Saint-Jacques, Saint-Jean-Pied-de-Port (UNESCO)', 'nl' => 'Porte Saint-Jacques, Saint-Jean-Pied-de-Port (UNESCO)', 'de' => 'Porte Saint-Jacques, Saint-Jean-Pied-de-Port (UNESCO)'],
                'type' => 'poi',
                'poi_category' => 'fortress',
                'latitude' => 43.1636,
                'longitude' => -1.2367,
                'detour_type' => 'on_path',
                'detour_distance_km' => 0.00,
                'visit_duration_min' => 30,
                'entry_cost_eur' => 0.00,
                'booking_required' => false,
                'description' => [
                    'fr' => 'Porte UNESCO des chemins de Saint-Jacques. Citadelle Vauban, rue de la Citadelle, pont romain sur la Nive. Accueil pèlerin 39 rue de la Citadelle (credencial n°3, statistiques). Fin du tronçon France — ~1 350 km depuis Liège.',
                    'nl' => 'UNESCO-poort van de Jacobswegen. Vauban-citadel, rue de la Citadelle, Romeinse brug over de Nive. Pelgrimsopvang nr. 39 rue de la Citadelle (credencial nr. 3). Einde Frans traject — ~1 350 km vanaf Luik.',
                    'de' => 'UNESCO-Tor der Jakobswege. Vauban-Zitadelle, Rue de la Citadelle, Römerbrücke über die Nive. Pilgerempfang Nr. 39 Rue de la Citadelle (Credencial Nr. 3). Ende des französischen Abschnitts — ~1 350 km ab Lüttich.',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($waypoints as $waypoint) {
            Waypoint::updateOrCreate(
                ['slug' => $waypoint['slug']],
                $waypoint
            );
        }

        $this->command->info(sprintf('WaypointSeederFrance : %d waypoints FR créés/mis à jour.', count($waypoints)));
    }
}
