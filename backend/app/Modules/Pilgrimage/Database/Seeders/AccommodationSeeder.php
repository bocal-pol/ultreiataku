<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Accommodation;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Seed hébergements réels Belgique (carnet-belgique.md).
 * Idempotent via updateOrCreate sur (stage_id + name JSON fr).
 */
class AccommodationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('AccommodationSeeder — démarrage');

        // Charger les stages par code (lookup)
        $stagesByCode = Stage::pluck('id', 'code');

        $accommodations = [
            // ─── J0 — Liège (avant départ) ──────────────────────────────────
            [
                'stage_code' => 'BE-01', // associé J1 mais pré-départ Liège
                'data' => [
                    'name' => ['fr' => 'AJ Simenon Liège', 'nl' => 'JH Simenon Luik', 'de' => 'JH Simenon Lüttich'],
                    'type' => 'hostel',
                    'address' => 'Rue Georges Simenon 2, 4020 Liège',
                    'website' => 'https://www.aubergesdejeunesse.be/',
                    'price_min_eur' => 25.00,
                    'price_max_eur' => 30.00,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'stamps_credencial' => false,
                    'pilgrim_friendly' => true,
                    'is_primary' => false,
                    'sort_order' => 99,
                    'notes' => [
                        'fr' => 'Recommandé pour la nuit J-1 avant départ. Tampon crédential à la Cathédrale Saint-Paul.',
                        'nl' => 'Aanbevolen voor nacht J-1 voor vertrek. Stempel bij de Sint-Pauluskathedraal.',
                        'de' => 'Empfohlen für die Nacht J-1 vor der Abreise. Stempel in der Kathedrale Saint-Paul.',
                    ],
                    'verified_at' => now()->subMonths(2),
                ],
            ],

            // ─── J1 — Amay ──────────────────────────────────────────────────
            [
                'stage_code' => 'BE-01',
                'data' => [
                    'name' => ['fr' => 'Gîte paroissial Amay', 'nl' => 'Parochiegîte Amay', 'de' => 'Pfarrei-Gîte Amay'],
                    'type' => 'donativo',
                    'address' => 'Collégiale Sainte-Ode et Saint-Georges, Amay',
                    'phone' => '+32 85 31 25 22',
                    'price_min_eur' => 5.00,
                    'price_max_eur' => 10.00,
                    'is_donativo' => true,
                    'capacity' => 6,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 2,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => '⚠️ Prévenir 24-48h à l\'avance — capacité limitée 4-6 places. Contact via cure d\'Amay.',
                        'nl' => '⚠️ 24-48u vooraf verwittigen — beperkte capaciteit 4-6 plaatsen.',
                        'de' => '⚠️ 24-48h vorher anmelden — begrenzte Kapazität 4-6 Plätze.',
                    ],
                    'bivouac_legal' => true,
                    'bivouac_notes' => [
                        'fr' => 'Zone RAVeL rive gauche Meuse, discrètement. Respecter LNT. Fontaine cimetière Amay.',
                        'nl' => 'Zone RAVeL linkeroever Maas, discreet. LNT respecteren.',
                        'de' => 'RAVeL-Zone linkes Maasufer, diskret. LNT einhalten.',
                    ],
                    'verified_at' => now()->subMonths(1),
                ],
            ],

            // ─── J2 — Huy ────────────────────────────────────────────────────
            [
                'stage_code' => 'BE-02',
                'data' => [
                    'name' => ['fr' => 'Gîte des Compagnons', 'nl' => 'Gezellenherberg', 'de' => 'Gîte des Compagnons'],
                    'type' => 'donativo',
                    'address' => 'Association des Compagnons du Chemin de Saint-Jacques, Huy',
                    'website' => 'https://www.st-jacques.be/',
                    'price_min_eur' => 10.00,
                    'price_max_eur' => 15.00,
                    'is_donativo' => true,
                    'capacity' => 8,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'booking_notice_days' => 1,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => '⚠️ Réserver à l\'avance. 6-8 places. Association Belge des Amis de Saint-Jacques.',
                        'nl' => '⚠️ Vooraf reserveren. 6-8 plaatsen.',
                        'de' => '⚠️ Vorher reservieren. 6-8 Plätze.',
                    ],
                    'verified_at' => now()->subMonths(3),
                ],
            ],
            [
                'stage_code' => 'BE-02',
                'data' => [
                    'name' => ['fr' => 'Camping Sandaya Huy', 'nl' => 'Camping Sandaya Hoei', 'de' => 'Camping Sandaya Huy'],
                    'type' => 'camping',
                    'address' => 'Rue Chaussée Napoléon, Huy',
                    'price_min_eur' => 15.00,
                    'price_max_eur' => 20.00,
                    'has_shower' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => false,
                    'sort_order' => 2,
                    'notes' => [
                        'fr' => 'Alternative camping. 15-20 € tente.',
                        'nl' => 'Camping alternatief.',
                        'de' => 'Camping-Alternative.',
                    ],
                    'verified_at' => now()->subMonths(5),
                ],
            ],

            // ─── J4 — Namur ──────────────────────────────────────────────────
            [
                'stage_code' => 'BE-04',
                'data' => [
                    'name' => ['fr' => 'AJ Félicien Rops Namur', 'nl' => 'JH Félicien Rops Namen', 'de' => 'JH Félicien Rops Namur'],
                    'type' => 'hostel',
                    'address' => 'Avenue Félicien Rops 8, 5000 Namur',
                    'website' => 'https://www.aubergesdejeunesse.be/',
                    'price_min_eur' => 25.00,
                    'price_max_eur' => 30.00,
                    'capacity' => 40,
                    'has_shower' => true,
                    'has_wifi' => true,
                    'stamps_credencial' => false,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Dortoir 4-6 lits. Point vieille ville, proche citadelle. Tampon crédential à la Cathédrale Saint-Aubain.',
                        'nl' => 'Slaapzaal 4-6 bedden. Centraal gelegen, dicht bij de citadel.',
                        'de' => 'Schlafsaal 4-6 Betten. Zentral gelegen, nahe Zitadelle.',
                    ],
                    'verified_at' => now()->subMonths(2),
                ],
            ],
            [
                'stage_code' => 'BE-04',
                'data' => [
                    'name' => ['fr' => 'Accueil paroissial Cathédrale Saint-Aubain', 'nl' => 'Parochiale opvang Kathedraal Sint-Aubain', 'de' => 'Pfarrlicher Empfang Kathedrale Saint-Aubain'],
                    'type' => 'donativo',
                    'phone' => '+32 81 22 34 15',
                    'is_donativo' => true,
                    'has_shower' => false,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => false,
                    'sort_order' => 2,
                    'notes' => [
                        'fr' => 'Donativo. Se présenter en fin d\'après-midi. Doyenné de Namur.',
                        'nl' => 'Donativo. \'s Avonds aankomen.',
                        'de' => 'Donativo. Nachmittags ankommen.',
                    ],
                    'verified_at' => null, // À vérifier — RG-08
                ],
            ],

            // ─── J5 — Yvoir ──────────────────────────────────────────────────
            [
                'stage_code' => 'BE-05',
                'data' => [
                    'name' => ['fr' => 'Camping Les Trieux', 'nl' => 'Camping Les Trieux', 'de' => 'Camping Les Trieux'],
                    'type' => 'camping',
                    'address' => 'Yvoir',
                    'price_min_eur' => 15.00,
                    'price_max_eur' => 20.00,
                    'capacity' => 50,
                    'has_shower' => true,
                    'stamps_credencial' => false,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'bivouac_legal' => true,
                    'bivouac_notes' => [
                        'fr' => 'Zone rive Meuse en amont Yvoir (bosquets), bord RAVeL discret. Vue Rochers de Freÿr.',
                        'nl' => 'Zone Maasoeverkant stroomopwaarts Yvoir, discreet RAVeL-pad.',
                        'de' => 'Zone Maasufer aufwärts Yvoir, diskret am RAVeL-Weg.',
                    ],
                    'verified_at' => now()->subMonths(8), // > 6 mois → badge orange
                ],
            ],

            // ─── J6 — Dinant ─────────────────────────────────────────────────
            [
                'stage_code' => 'BE-06',
                'data' => [
                    'name' => ['fr' => 'Gîte paroissial Notre-Dame de Dinant', 'nl' => 'Parochiegîte Onze-Lieve-Vrouw Dinant', 'de' => 'Pfarrei-Gîte Notre-Dame Dinant'],
                    'type' => 'donativo',
                    'phone' => '+32 82 22 91 44',
                    'address' => 'Collégiale Notre-Dame de Dinant',
                    'price_min_eur' => 10.00,
                    'price_max_eur' => 15.00,
                    'is_donativo' => true,
                    'capacity' => 6,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'booking_required' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => '⚠️ Capacité 4-6 places, prévenir. Via Collégiale Notre-Dame de Dinant.',
                        'nl' => '⚠️ Capaciteit 4-6 plaatsen, vooraf verwittigen.',
                        'de' => '⚠️ Kapazität 4-6 Plätze, vorab anmelden.',
                    ],
                    'verified_at' => now()->subMonths(4),
                ],
            ],

            // ─── J7 — Hastière ───────────────────────────────────────────────
            [
                'stage_code' => 'BE-07',
                'data' => [
                    'name' => ['fr' => 'Gîte de l\'Abbaye Notre-Dame d\'Hastière', 'nl' => 'Gîte Abdij Onze-Lieve-Vrouw Hastière', 'de' => 'Gîte Abtei Hastière'],
                    'type' => 'abbey',
                    'address' => 'Abbaye d\'Hastière, Hastière',
                    'phone' => '+32 82 64 44 30',
                    'price_min_eur' => 10.00,
                    'price_max_eur' => 15.00,
                    'is_donativo' => true,
                    'capacity' => 12,
                    'has_shower' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Ambiance monastique, silence requis. Repas partagé possible. Contacter l\'abbaye à l\'avance.',
                        'nl' => 'Kloostersfeer, stilte vereist. Gedeelde maaltijd mogelijk.',
                        'de' => 'Klosteratmosphäre, Stille erforderlich. Gemeinsames Essen möglich.',
                    ],
                    'bivouac_legal' => true,
                    'bivouac_notes' => [
                        'fr' => 'Aire pique-nique Meuse (Waulsort) — 1 nuit discrète tolérée. Fontaine village.',
                        'nl' => 'Picknickplaats Maas (Waulsort) — 1 nacht discreet toegestaan.',
                        'de' => 'Picknickplatz Maas (Waulsort) — 1 Nacht diskret erlaubt.',
                    ],
                    'verified_at' => now()->subMonths(1),
                ],
            ],

            // ─── J8 — Givet (France) ─────────────────────────────────────────
            [
                'stage_code' => 'BE-08',
                'data' => [
                    'name' => ['fr' => 'Camping du Bout du Monde', 'nl' => 'Camping du Bout du Monde', 'de' => 'Camping du Bout du Monde'],
                    'type' => 'camping',
                    'address' => 'Rue de la Meuse, 08600 Givet, France',
                    'phone' => '+33 3 24 42 30 20',
                    'price_min_eur' => 12.00,
                    'price_max_eur' => 18.00,
                    'capacity' => 30,
                    'has_shower' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Cadre superbe rive Meuse. WiFi partiel. Aire camping-car à côté.',
                        'nl' => 'Prachtige omgeving aan de Maas. Gedeeltelijk WiFi.',
                        'de' => 'Herrliche Lage am Maasufer. Teilweise WLAN.',
                    ],
                    'verified_at' => now()->subMonths(3),
                ],
            ],

            // ─── J9 — Doische ────────────────────────────────────────────────
            [
                'stage_code' => 'BE-09',
                'data' => [
                    'name' => ['fr' => 'Chambres d\'hôtes Doische', 'nl' => 'Bed & Breakfast Doische', 'de' => 'Gästezimmer Doische'],
                    'type' => 'gite',
                    'phone' => '+32 60 39 04 63',
                    'price_min_eur' => 50.00,
                    'price_max_eur' => 70.00,
                    'has_shower' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Via Office Tourisme Viroinval : 060 39 04 63. Zone réseau limité — prévenir famille avant.',
                        'nl' => 'Via Toerismebureau Viroinval. Beperkt netwerk — familie verwittigen.',
                        'de' => 'Über Tourismusbüro Viroinval. Eingeschränktes Netz — Familie informieren.',
                    ],
                    'bivouac_legal' => true,
                    'bivouac_notes' => [
                        'fr' => 'Lisière de Fagne, à l\'écart du village. Vérifier DNF. Ne PAS boire l\'eau de tourbière.',
                        'nl' => 'Bosrand Fagne, buiten het dorp. DNF checken. GEEN veenwater drinken.',
                        'de' => 'Waldrand Fagne, außerhalb des Dorfes. DNF überprüfen. Kein Moorwasser trinken.',
                    ],
                    'verified_at' => null, // À vérifier
                ],
            ],

            // ─── J10 — Olloy-sur-Viroin ──────────────────────────────────────
            [
                'stage_code' => 'BE-10',
                'data' => [
                    'name' => ['fr' => 'AJ Vierves-sur-Viroin', 'nl' => 'JH Vierves-sur-Viroin', 'de' => 'JH Vierves-sur-Viroin'],
                    'type' => 'hostel',
                    'phone' => '+32 60 39 04 63',
                    'price_min_eur' => 25.00,
                    'price_max_eur' => 30.00,
                    'has_shower' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => '⚠️ RÉSERVE NATURELLE : bivouac interdit dans la Réserve Viroin-Hermeton. Bivouaquer en forêt communale uniquement.',
                        'nl' => '⚠️ NATUURRESERVAAT: bivakkeren verboden in het Viroin-Hermeton reservaat.',
                        'de' => '⚠️ NATURSCHUTZGEBIET: Biwakieren im Viroin-Hermeton-Reservat verboten.',
                    ],
                    'verified_at' => now()->subMonths(7), // > 6 mois → badge orange
                ],
            ],

            // ─── J11 — Couvin ────────────────────────────────────────────────
            [
                'stage_code' => 'BE-11',
                'data' => [
                    'name' => ['fr' => 'Gîte de la Fagne', 'nl' => 'Gîte de la Fagne', 'de' => 'Gîte de la Fagne'],
                    'type' => 'gite',
                    'phone' => '+32 60 34 01 40',
                    'price_min_eur' => 30.00,
                    'price_max_eur' => 45.00,
                    'has_shower' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'bivouac_legal' => true,
                    'bivouac_notes' => [
                        'fr' => 'Zone bord de l\'Eau Noire, en dehors du centre. Fontaine village ou rivière (filtrer !). Éviter tourbières.',
                        'nl' => 'Zone langs de Eau Noire, buiten het centrum. Rivierwater filteren!',
                        'de' => 'Zone entlang der Eau Noire, außerhalb des Zentrums. Flusswasser filtern!',
                    ],
                    'notes' => [
                        'fr' => 'Via Office Tourisme Couvin : 060 34 01 40. Alternative : Camping Le Roptai (Nismes, 5 km avant).',
                        'nl' => 'Via Toerismebureau Couvin.',
                        'de' => 'Über Tourismusbüro Couvin.',
                    ],
                    'verified_at' => now()->subMonths(2),
                ],
            ],

            // ─── J12 — Rocroi (France) ───────────────────────────────────────
            [
                'stage_code' => 'BE-12',
                'data' => [
                    'name' => ['fr' => 'Gîte donativo Rocroi', 'nl' => 'Donativo Gîte Rocroi', 'de' => 'Donativo Gîte Rocroi'],
                    'type' => 'donativo',
                    'address' => 'Intra-muros, dans l\'étoile Vauban, Rocroi, France',
                    'is_donativo' => true,
                    'has_shower' => true,
                    'has_kitchen' => true,
                    'stamps_credencial' => true,
                    'pilgrim_friendly' => true,
                    'is_primary' => true,
                    'sort_order' => 1,
                    'notes' => [
                        'fr' => 'Association des Amis de Saint-Jacques des Ardennes. Adresse à confirmer intra-muros. Convivialité pèlerine — grand moment de sociabilité.',
                        'nl' => 'Vereniging Vrienden van Sint-Jacob der Ardennen. Adres bevestigen.',
                        'de' => 'Verein Freunde des Jakobswegs in den Ardennen. Adresse bestätigen.',
                    ],
                    'verified_at' => null, // À vérifier
                ],
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($accommodations as $item) {
            $stageId = $stagesByCode[$item['stage_code']] ?? null;

            if ($stageId === null) {
                $this->command->warn("AccommodationSeeder : stage {$item['stage_code']} introuvable — skipping.");
                continue;
            }

            // Identifier par stage_id + name FR (clé naturelle stable)
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

        Log::info('AccommodationSeeder terminé', ['created' => $created, 'updated' => $updated]);
        $this->command->info("AccommodationSeeder : {$created} créés, {$updated} mis à jour.");
    }
}
