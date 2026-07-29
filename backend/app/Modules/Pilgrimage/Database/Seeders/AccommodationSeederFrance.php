<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Accommodation;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Hébergements France par segment (hebergement/carnet-france.md).
 * Idempotent via whereJsonContains sur name.fr + stage_id.
 * 1-2 hébergements représentatifs par étape-clé (conformément au carnet).
 */
class AccommodationSeederFrance extends Seeder
{
    public function run(): void
    {
        $this->command->info('AccommodationSeederFrance — démarrage');

        $stagesByCode = Stage::pluck('id', 'code');

        $accommodations = [
            // ─── FR-01 Rocroi → Signy ───────────────────────────────────────
            [
                'stage_code' => 'FR-01',
                'data' => [
                    'name' => ['fr' => 'Gîte d\'étape de Signy-l\'Abbaye', 'nl' => 'Gîte d\'étape Signy-l\'Abbaye', 'de' => 'Gîte d\'étape Signy-l\'Abbaye'],
                    'type' => 'gite',
                    'address' => 'Signy-l\'Abbaye, Ardennes (08)',
                    'price_min_eur' => 15.00,
                    'price_max_eur' => 25.00,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'Gîte d\'étape communal. Réserver la veille par téléphone (norme française).', 'nl' => 'Gemeentelijk gîte. Dag ervoor telefonisch reserveren (Franse norm).', 'de' => 'Kommunales Gîte. Am Vortag telefonisch reservieren (französische Norm).'],
                    'bivouac_legal' => true,
                    'bivouac_notes' => ['fr' => 'Lisières de forêts ardennaises — hors lignes de chasse à partir de septembre.', 'nl' => 'Bosranden Ardennen — buiten jachtlijnen vanaf september.', 'de' => 'Waldränder Ardennen — außerhalb Jagdlinien ab September.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-03 → Reims R1 ────────────────────────────────────────────
            [
                'stage_code' => 'FR-03',
                'data' => [
                    'name' => ['fr' => 'CIS de Champagne Reims', 'nl' => 'CIS de Champagne Reims', 'de' => 'CIS de Champagne Reims'],
                    'type' => 'hostel',
                    'address' => 'Chaussée Bocquaine, 51100 Reims',
                    'website' => 'https://www.cis-de-champagne.fr/',
                    'price_min_eur' => 28.00,
                    'price_max_eur' => 35.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'pilgrim_friendly' => true,
                    'stamps_credencial' => false,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'Auberge ~30 €. Tampon credencial à la sacristie de la cathédrale Notre-Dame.', 'nl' => 'Herberg ~30 €. Stempel bij de sacristie van de kathedraal.', 'de' => 'Herberge ~30 €. Stempel in der Sakristei der Kathedrale.'],
                    'verified_at' => null,
                ],
            ],
            [
                'stage_code' => 'FR-03',
                'data' => [
                    'name' => ['fr' => 'Accueil diocésain Reims (Maison Saint-Sixte)', 'nl' => 'Diocesane opvang Reims (Maison Saint-Sixte)', 'de' => 'Diözesane Aufnahme Reims (Maison Saint-Sixte)'],
                    'type' => 'donativo',
                    'is_donativo' => true,
                    'has_shower' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => false,
                    'sort_order' => 2,
                    'notes' => ['fr' => 'Donativo — se renseigner à l\'arrivée (disponibilité non garantie). Accueil chaleureux pèlerin.', 'nl' => 'Donativo — bij aankomst informeren (beschikbaarheid niet gegarandeerd).', 'de' => 'Donativo — bei Ankunft erfragen (Verfügbarkeit nicht garantiert).'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-04 → Verzy ───────────────────────────────────────────────
            [
                'stage_code' => 'FR-04',
                'data' => [
                    'name' => ['fr' => 'Chambre d\'hôtes vigneronne Verzy', 'nl' => 'Wijnboer bed & breakfast Verzy', 'de' => 'Winzer-Gästezimmer Verzy'],
                    'type' => 'gite',
                    'address' => 'Verzy, Marne (51)',
                    'price_min_eur' => 55.00,
                    'price_max_eur' => 75.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 2,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'Nuit stratégique pour la boucle des Faux au matin. ⚠️ Zone touristique champagne — réserver 2-3 jours à l\'avance. Dégustation champagne de vigneron possible.', 'nl' => 'Strategische nacht voor de Faux-lus \'s ochtends. ⚠️ Champagne toeristenzone — 2-3 dagen vooraf reserveren.', 'de' => 'Strategische Übernachtung für die Faux-Runde am Morgen. ⚠️ Champagne-Tourismuszone — 2-3 Tage voraus reservieren.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-05 → Châlons ─────────────────────────────────────────────
            [
                'stage_code' => 'FR-05',
                'data' => [
                    'name' => ['fr' => 'Accueil paroissial Châlons-en-Champagne (cathédrale)', 'nl' => 'Parochiale opvang Châlons-en-Champagne (kathedraal)', 'de' => 'Pfarrliche Aufnahme Châlons-en-Champagne (Kathedrale)'],
                    'type' => 'donativo',
                    'is_donativo' => true,
                    'has_shower' => false,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'Accueil paroissial cathédrale Saint-Étienne. Tampon credencial sur place.', 'nl' => 'Parochiale opvang kathedraal Saint-Étienne. Stempel ter plaatse.', 'de' => 'Pfarrliche Aufnahme Kathedrale Saint-Étienne. Pilgerstempel vor Ort.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-07 → Lac du Der ──────────────────────────────────────────
            [
                'stage_code' => 'FR-07',
                'data' => [
                    'name' => ['fr' => 'Camping du Lac du Der (Giffaumont)', 'nl' => 'Camping Lac du Der (Giffaumont)', 'de' => 'Camping Lac du Der (Giffaumont)'],
                    'type' => 'camping',
                    'address' => 'Giffaumont-Champaubert, Marne (51)',
                    'price_min_eur' => 12.00,
                    'price_max_eur' => 18.00,
                    'has_shower' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'Plusieurs campings bord du lac. Réserver en juillet-août. Supérette saisonnière.', 'nl' => 'Meerdere campings aan het meer. Reserveren in juli-augustus. Seizoenswinkel.', 'de' => 'Mehrere Campingplätze am See. In Juli-August reservieren. Saisonaler Supermarkt.'],
                    'bivouac_legal' => true,
                    'bivouac_notes' => ['fr' => 'Zone rurale calme : bivouac discret possible hors abords immédiats du lac.', 'nl' => 'Rustige landelijke zone: discreet bivakkeren mogelijk buiten de directe omgeving van het meer.', 'de' => 'Ruhige ländliche Zone: diskretes Biwak möglich außerhalb der unmittelbaren Seeumgebung.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-13 → Auxerre ────────────────────────────────────────────
            [
                'stage_code' => 'FR-13',
                'data' => [
                    'name' => ['fr' => 'Auberge de Jeunesse Auxerre', 'nl' => 'Jeugdherberg Auxerre', 'de' => 'Jugendherberge Auxerre'],
                    'type' => 'hostel',
                    'address' => 'Auxerre, Yonne (89)',
                    'price_min_eur' => 23.00,
                    'price_max_eur' => 28.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'AJ ~25 €. Accueil paroissial disponible. Marchés mar/ven/dim.', 'nl' => 'JH ~25 €. Parochiale opvang beschikbaar. Markten di/vr/zo.', 'de' => 'JH ~25 €. Pfarrliche Aufnahme verfügbar. Märkte Di/Fr/So.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-15 → Vézelay R2 ──────────────────────────────────────────
            [
                'stage_code' => 'FR-15',
                'data' => [
                    'name' => ['fr' => 'Hôtellerie de Vézelay — Fraternités monastiques de Jérusalem', 'nl' => 'Hôtellerie de Vézelay — Fraterni Monniken van Jeruzalem', 'de' => 'Hôtellerie de Vézelay — Jerusalemer Mönchsgemeinschaft'],
                    'type' => 'abbey',
                    'address' => 'Vézelay, Yonne (89)',
                    'website' => 'https://hotellerie-vezelay.fr/pelerins/',
                    'price_min_eur' => 25.00,
                    'price_max_eur' => 35.00,
                    'has_shower' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 3,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => '⭐ Accueil pèlerin des Fraternités monastiques de Jérusalem. Messe/vêpres avec les Fraternités. Credencial n°2 ici. ⚠️ Réserver 2-3 jours avant (haut lieu jacquaire).', 'nl' => '⭐ Pelgrimsopvang van de Fraterni Monniken van Jeruzalem. Mis/vespers. Credencial nr. 2 hier. ⚠️ 2-3 dagen vooraf reserveren.', 'de' => '⭐ Pilgeraufnahme der Jerusalemer Mönchsgemeinschaft. Messe/Vesper. Credencial Nr. 2 hier. ⚠️ 2-3 Tage voraus reservieren.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-17 → La Charité ───────────────────────────────────────────
            [
                'stage_code' => 'FR-17',
                'data' => [
                    'name' => ['fr' => 'Accueil pèlerin du Prieuré, La Charité-sur-Loire', 'nl' => 'Pelgrimsopvang van de Priorij, La Charité-sur-Loire', 'de' => 'Pilgeraufnahme des Priorats, La Charité-sur-Loire'],
                    'type' => 'donativo',
                    'address' => 'Prieuré Notre-Dame, La Charité-sur-Loire, Nièvre (58)',
                    'is_donativo' => true,
                    'has_shower' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => '⭐ Accueil pèlerin donativo au prieuré clunisien (UNESCO). Ambiance pèlerine authentique.', 'nl' => '⭐ Donativo pelgrimsopvang bij het cluniacenzerpriorij (UNESCO). Authentieke pelgrimssfeer.', 'de' => '⭐ Donativo-Pilgeraufnahme im Cluniazenserpriorat (UNESCO). Authentische Pilgeratmosphäre.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-18 → Bourges R3 ──────────────────────────────────────────
            [
                'stage_code' => 'FR-18',
                'data' => [
                    'name' => ['fr' => 'Auberge de Jeunesse Cujas Bourges', 'nl' => 'Jeugdherberg Cujas Bourges', 'de' => 'Jugendherberge Cujas Bourges'],
                    'type' => 'hostel',
                    'address' => 'Bourges, Cher (18)',
                    'price_min_eur' => 23.00,
                    'price_max_eur' => 28.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'AJ Cujas ~25 €. Accueil diocésain disponible. Repos R3. Magasins de sport (bilan matériel mi-parcours).', 'nl' => 'JH Cujas ~25 €. Diocesane opvang beschikbaar. Rustdag R3.', 'de' => 'JH Cujas ~25 €. Diözesane Aufnahme verfügbar. Ruhetag R3.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-25 → Oradour ────────────────────────────────────────────
            [
                'stage_code' => 'FR-25',
                'data' => [
                    'name' => ['fr' => 'Chambre d\'hôtes Oradour / Saint-Junien', 'nl' => 'Bed & Breakfast Oradour / Saint-Junien', 'de' => 'Gästezimmer Oradour / Saint-Junien'],
                    'type' => 'gite',
                    'address' => 'Oradour-sur-Glane ou Saint-Junien (6 km), Haute-Vienne (87)',
                    'price_min_eur' => 55.00,
                    'price_max_eur' => 75.00,
                    'has_shower' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 1,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'Zone variante ouest — moins équipée en refuges. ⚠️ Téléphoner la veille impérativement. Saint-Junien (6 km) = alternative si complet à Oradour.', 'nl' => 'Westelijke variant — minder refuges. ⚠️ Dag ervoor bellen verplicht. Saint-Junien (6 km) = alternatief indien vol.', 'de' => 'Westvariante — weniger Refuges. ⚠️ Am Vortag unbedingt anrufen. Saint-Junien (6 km) = Alternative wenn Oradour voll.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-26 → Limoges R4 ──────────────────────────────────────────
            [
                'stage_code' => 'FR-26',
                'data' => [
                    'name' => ['fr' => 'Auberge de Jeunesse Limoges', 'nl' => 'Jeugdherberg Limoges', 'de' => 'Jugendherberge Limoges'],
                    'type' => 'hostel',
                    'address' => 'Limoges, Haute-Vienne (87)',
                    'price_min_eur' => 23.00,
                    'price_max_eur' => 28.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'AJ ~25 €. Repos R4 (post-Oradour — digérer l\'émotion). 2e paire de chaussures : Décathlon/Intersport sur place (~1 100 km cumulés).', 'nl' => 'JH ~25 €. Rustdag R4 (na Oradour). 2e paar schoenen: Decathlon/Intersport ter plaatse.', 'de' => 'JH ~25 €. Ruhetag R4 (nach Oradour). 2. Paar Schuhe: Decathlon/Intersport vor Ort.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-29 → Périgueux R5 ────────────────────────────────────────
            [
                'stage_code' => 'FR-29',
                'data' => [
                    'name' => ['fr' => 'Accueil pèlerin cathédrale Saint-Front, Périgueux', 'nl' => 'Pelgrimsopvang kathedraal Saint-Front, Périgueux', 'de' => 'Pilgeraufnahme Kathedrale Saint-Front, Périgueux'],
                    'type' => 'donativo',
                    'address' => 'Périgueux, Dordogne (24)',
                    'is_donativo' => true,
                    'has_shower' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'Accueil pèlerin + refuge associatif. Repos R5. Marché place du Coderc (mer/sam). ⭐ Le cœur du réseau associatif — Sorges (refuge ouvert jusqu\'au 31 oct) + Périgueux.', 'nl' => 'Pelgrimsopvang + associatief refuge. Rustdag R5. Markt (wo/za). ⭐ Hart van het associatief netwerk.', 'de' => 'Pilgeraufnahme + Vereinsrefuge. Ruhetag R5. Markt (Mi/Sa). ⭐ Herz des Vereinsnetzes.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-32 → Bazas ──────────────────────────────────────────────
            [
                'stage_code' => 'FR-32',
                'data' => [
                    'name' => ['fr' => 'Gîte communal Bazas', 'nl' => 'Gemeentelijk gîte Bazas', 'de' => 'Kommunales Gîte Bazas'],
                    'type' => 'gite',
                    'address' => 'Bazas, Gironde (33)',
                    'price_min_eur' => 15.00,
                    'price_max_eur' => 20.00,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => '⚠️ Dernier vrai ravitaillement avant les Landes (Super U + marché sam). Ravitailler pour 48h ici.', 'nl' => '⚠️ Laatste echte ravitaillering voor de Landes (Super U + zaterdagmarkt). Hier voor 48u ravitailleren.', 'de' => '⚠️ Letzte echte Verproviantierung vor den Landes (Super U + Samstagmarkt). Hier für 48h verproviantieren.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-34 → Mont-de-Marsan R6 ───────────────────────────────────
            [
                'stage_code' => 'FR-34',
                'data' => [
                    'name' => ['fr' => 'Hôtel / Camping Mont-de-Marsan', 'nl' => 'Hotel / Camping Mont-de-Marsan', 'de' => 'Hotel / Camping Mont-de-Marsan'],
                    'type' => 'gite',
                    'address' => 'Mont-de-Marsan, Landes (40)',
                    'price_min_eur' => 20.00,
                    'price_max_eur' => 60.00,
                    'has_shower' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => 'Repos R6. Marchés mar/sam. Dernière grande ville avant le Béarn. Hôtels + camping municipal.', 'nl' => 'Rustdag R6. Markten di/za. Laatste grote stad voor het Béarn.', 'de' => 'Ruhetag R6. Märkte Di/Sa. Letzte große Stadt vor dem Béarn.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-39 → Ostabat ────────────────────────────────────────────
            [
                'stage_code' => 'FR-39',
                'data' => [
                    'name' => ['fr' => 'Gîte historique Maison Ospitalia, Ostabat-Asme', 'nl' => 'Historisch gîte Maison Ospitalia, Ostabat-Asme', 'de' => 'Historisches Gîte Maison Ospitalia, Ostabat-Asme'],
                    'type' => 'gite',
                    'address' => 'Ostabat-Asme, Pyrénées-Atlantiques (64)',
                    'price_min_eur' => 18.00,
                    'price_max_eur' => 25.00,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => '⭐ Gîte historique médiéval. Ambiance pèlerine très forte. Chants basques au dîner si chance. Réserver à l\'avance.', 'nl' => '⭐ Historisch middeleeuws gîte. Zeer sterke pelgrimssfeer. Baskische liederen bij het avondeten als je geluk hebt.', 'de' => '⭐ Historisches mittelalterliches Gîte. Sehr starke Pilgeratmosphäre. Baskische Lieder beim Abendessen wenn Glück.'],
                    'verified_at' => null,
                ],
            ],

            // ─── FR-40 → SJPP R7-R8 ─────────────────────────────────────────
            [
                'stage_code' => 'FR-40',
                'data' => [
                    'name' => ['fr' => 'Gîte pèlerin rue de la Citadelle, Saint-Jean-Pied-de-Port', 'nl' => 'Pelgrimsgîte rue de la Citadelle, Saint-Jean-Pied-de-Port', 'de' => 'Pilgergîte Rue de la Citadelle, Saint-Jean-Pied-de-Port'],
                    'type' => 'gite',
                    'address' => 'Rue de la Citadelle, Saint-Jean-Pied-de-Port, Pyrénées-Atlantiques (64)',
                    'price_min_eur' => 18.00,
                    'price_max_eur' => 30.00,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 3,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => ['fr' => '⭐ Nombreux gîtes rue de la Citadelle. ⚠️ Réserver 2-3 jours avant (mai-sept très fréquenté). L\'Accueil pèlerin du 39 rue de la Citadelle oriente. 2 nuits R7-R8. Credencial n°3 + recharge complète (Compeed, gaz, chaussettes). Fin tronçon France — ~1 350 km depuis Liège.', 'nl' => '⭐ Veel gîtes rue de la Citadelle. ⚠️ 2-3 dagen vooraf reserveren (mei-sept zeer druk). Pelgrimsopvang nr. 39 informeert. 2 nachten R7-R8. Credencial nr. 3 + volledige aanvulling.', 'de' => '⭐ Viele Gîtes Rue de la Citadelle. ⚠️ 2-3 Tage voraus reservieren (Mai-Sept sehr viel Betrieb). Pilgerempfang Nr. 39 hilft. 2 Nächte R7-R8. Credencial Nr. 3 + vollständige Aufstockung.'],
                    'verified_at' => null,
                ],
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($accommodations as $item) {
            $stageId = $stagesByCode[$item['stage_code']] ?? null;

            if ($stageId === null) {
                $this->command->warn("AccommodationSeederFrance : stage {$item['stage_code']} introuvable — skipping.");

                continue;
            }

            $nameFr = $item['data']['name']['fr'];

            $existing = Accommodation::where('stage_id', $stageId)
                ->whereJsonContains('name->fr', $nameFr)
                ->first();

            if ($existing !== null) {
                $existing->update(array_merge(['stage_id' => $stageId], $item['data']));
                $updated++;
            } else {
                Accommodation::create(array_merge(['stage_id' => $stageId], $item['data']));
                $created++;
            }
        }

        Log::info('AccommodationSeederFrance terminé', ['created' => $created, 'updated' => $updated]);
        $this->command->info("AccommodationSeederFrance : {$created} créés, {$updated} mis à jour.");
    }
}
