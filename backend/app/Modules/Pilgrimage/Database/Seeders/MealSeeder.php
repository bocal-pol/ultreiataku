<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Meal;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Seed repas signatures Belgique (ou-manger-local-belgique.md + specialites-wallonnes-ardennes.md).
 * Idempotent via whereJsonContains sur name.fr + stage_id.
 */
class MealSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('MealSeeder — démarrage');

        $stagesByCode = Stage::pluck('id', 'code');

        $meals = [
            // ─── BE-01 — Liège → Amay ───────────────────────────────────────
            [
                'stage_code' => 'BE-01',
                'data' => [
                    'meal_type' => 'lunch',
                    'name' => ['fr' => 'Salade liégeoise', 'nl' => 'Luikse salade', 'de' => 'Lütticher Salat'],
                    'description' => [
                        'fr' => 'Haricots verts, lardons, oignons, vinaigre de vin. Plat emblématique de la cuisine liégeoise, servi tiède.',
                        'nl' => 'Sperziebonen, spekjes, uien, wijnazijn. Emblematisch gerecht van de Luikse keuken.',
                        'de' => 'Grüne Bohnen, Speckwürfel, Zwiebeln, Weinessig. Emblematisches Gericht der Lütticher Küche.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Café Le Vaudrée',
                    'restaurant_address' => 'Outremeuse, Liège',
                    'price_estimate_eur' => 12.00,
                    'kcal_estimate' => 750,
                    'notes' => [
                        'fr' => 'Repas de départ avant J1. + pékét en digestif si envie. 15-18 € complet.',
                        'nl' => 'Vertrekmaaltijd voor J1.',
                        'de' => 'Abschlussmahlzeit vor J1.',
                    ],
                ],
            ],

            // ─── BE-02 — Amay → Huy ─────────────────────────────────────────
            [
                'stage_code' => 'BE-02',
                'data' => [
                    'meal_type' => 'lunch',
                    'name' => ['fr' => 'Le Crompire — croquettes au fromage de Herve', 'nl' => 'Le Crompire — kroketten met Hervé-kaas', 'de' => 'Le Crompire — Kroketten mit Herve-Käse'],
                    'description' => [
                        'fr' => 'Friterie artisanale bio. Croquettes maison 3 sauces, pommes de terre bio Verlaine, boulettes maison.',
                        'nl' => 'Ambachtelijke bio-frietkraam. Huisgemaakte kroketten, bio-aardappelen Verlaine.',
                        'de' => 'Handwerkliche Bio-Fritüre. Hausgemachte Kroketten, Bio-Kartoffeln Verlaine.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Le Crompire',
                    'restaurant_address' => 'Rue Joseph Wauters 38, Huy (à vérifier si toujours à Huy)',
                    'price_estimate_eur' => 12.00,
                    'kcal_estimate' => 900,
                    'notes' => [
                        'fr' => '⚠️ Vérifier si toujours ouvert à Huy (déménagement possible à Fexhe-le-Haut-Clocher fin 2026). 12-18 €.',
                        'nl' => '⚠️ Controleren of nog open in Hoei.',
                        'de' => '⚠️ Prüfen ob noch in Huy geöffnet.',
                    ],
                ],
            ],
            [
                'stage_code' => 'BE-02',
                'data' => [
                    'meal_type' => 'breakfast',
                    'name' => ['fr' => 'Tarte al djote de Huy', 'nl' => 'Tarte al djote van Hoei', 'de' => 'Tarte al djote aus Huy'],
                    'description' => [
                        'fr' => 'Pâte briochée garnie de fromage (Boulot de Nivelles) et de bettes hachées avec persil. Introuvable ailleurs dans le monde.',
                        'nl' => 'Briochedeeg gevuld met kaas (Boulot de Nivelles) en gehakte snijbiet met peterselie. Uniek ter wereld.',
                        'de' => 'Briocheteig mit Käse (Boulot de Nivelles) und gehackten Mangold mit Petersilie. Weltweit einzigartig.',
                    ],
                    'meal_context' => 'grocery',
                    'restaurant_name' => 'Boulangerie du centre (Huy)',
                    'price_estimate_eur' => 4.00,
                    'kcal_estimate' => 400,
                    'notes' => [
                        'fr' => 'N\'existe QU\'À Huy. En part à la boulangerie. 3-5 €.',
                        'nl' => 'Bestaat ALLEEN in Hoei.',
                        'de' => 'Gibt es NUR in Huy.',
                    ],
                ],
            ],

            // ─── BE-03 — Huy → Andenne ──────────────────────────────────────
            [
                'stage_code' => 'BE-03',
                'data' => [
                    'meal_type' => 'breakfast',
                    'name' => ['fr' => 'Pistolet andennais', 'nl' => 'Andennais pistolet', 'de' => 'Andennais Pistolet'],
                    'description' => [
                        'fr' => 'Petit pain rond croustillant typique d\'Andenne, à la boulangerie locale avant de repartir.',
                        'nl' => 'Klein rond knapperig broodje typisch voor Andenne, bij de lokale bakker.',
                        'de' => 'Kleines rundes knuspriges Brötchen typisch für Andenne, beim lokalen Bäcker.',
                    ],
                    'meal_context' => 'grocery',
                    'restaurant_name' => 'Boulangerie andennaise centre',
                    'price_estimate_eur' => 3.00,
                    'kcal_estimate' => 250,
                    'notes' => [
                        'fr' => 'Spécialité locale à prendre au matin. 2-3 €.',
                        'nl' => 'Lokale specialiteit, \'s ochtends meenemen.',
                        'de' => 'Lokale Spezialität, morgens mitnehmen.',
                    ],
                ],
            ],

            // ─── BE-04 — Andenne → Namur ────────────────────────────────────
            [
                'stage_code' => 'BE-04',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Escavèche de Meuse', 'nl' => 'Escavèche van de Maas', 'de' => 'Escavèche von der Maas'],
                    'description' => [
                        'fr' => 'Poisson frit puis mariné dans le vinaigre avec oignons et laurier. Spécialité de la vallée de la Meuse.',
                        'nl' => 'Gefrituurd en dan gemarineerd in azijn met uien en laurier. Specialiteit van de Maasvallei.',
                        'de' => 'Gebratener Fisch, dann in Essig mit Zwiebeln und Lorbeer mariniert. Spezialität des Maasvalleys.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'La Cuve à Bière',
                    'restaurant_address' => 'Namur centre',
                    'price_estimate_eur' => 18.00,
                    'kcal_estimate' => 1100,
                    'notes' => [
                        'fr' => 'Dîner de repos recommandé à Namur. 15-22 €. Alternative : Chez Chen.',
                        'nl' => 'Aanbevolen rustdiner in Namen.',
                        'de' => 'Empfohlenes Ruhediner in Namur.',
                    ],
                ],
            ],

            // ─── BE-05 — Namur → Yvoir ──────────────────────────────────────
            [
                'stage_code' => 'BE-05',
                'data' => [
                    'meal_type' => 'snack',
                    'name' => ['fr' => 'Fraises de Wépion en direct producteur', 'nl' => 'Wépion-aardbeien rechtstreeks van de teler', 'de' => 'Wépion-Erdbeeren direkt vom Erzeuger'],
                    'description' => [
                        'fr' => 'Fraises IGP de Wépion, récoltées en mai-juin sur les coteaux de la Meuse. Étals de bord de route.',
                        'nl' => 'IGP aardbeien van Wépion, geoogst in mei-juni op de hellingen van de Maas.',
                        'de' => 'IGP-Erdbeeren aus Wépion, geerntet im Mai-Juni an den Maashängen.',
                    ],
                    'meal_context' => 'local_specialty',
                    'restaurant_address' => 'Étals N92 Wépion entre Namur et Yvoir (saison mai-juin)',
                    'price_estimate_eur' => 5.00,
                    'kcal_estimate' => 150,
                    'notes' => [
                        'fr' => 'En saison mai-juin uniquement. 4-5 € le ravier.',
                        'nl' => 'Alleen in het seizoen mei-juni.',
                        'de' => 'Nur in der Saison Mai-Juni.',
                    ],
                ],
            ],

            // ─── BE-06 — Yvoir → Dinant ─────────────────────────────────────
            [
                'stage_code' => 'BE-06',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Flamiche dinantaise + couque de Dinant', 'nl' => 'Dinantse flamiche + couque de Dinant', 'de' => 'Dinantser Flamiche + Couque de Dinant'],
                    'description' => [
                        'fr' => 'Flamiche : tarte salée chaude au fromage cuit sur pierre. Couque : biscuit très dur au miel dans moule bois sculpté. Le duo dinantais chaud/sec incontournable.',
                        'nl' => 'Flamiche: hete hartige kaastaat op steen. Couque: zeer hard honingkoekje in een gesneden houten vorm.',
                        'de' => 'Flamiche: heißer salziger Käsekuchen auf Stein. Couque: sehr hartes Honiggebäck in geschnitzter Holzform.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'La Broche + Maison Jacobs',
                    'restaurant_address' => 'La Broche (Dinant) + Rue Grande (couque, Maison Jacobs)',
                    'price_estimate_eur' => 15.00,
                    'kcal_estimate' => 1200,
                    'notes' => [
                        'fr' => 'Le duo dinantais incontournable. Flamiche 15-25 €. Couque 4-8 €. La couque se conserve des mois.',
                        'nl' => 'Het onmisbare duo van Dinant.',
                        'de' => 'Das unverzichtbare Dinanter Duo.',
                    ],
                ],
            ],

            // ─── BE-07 — Dinant → Hastière ──────────────────────────────────
            [
                'stage_code' => 'BE-07',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Fromage d\'abbaye d\'Hastière', 'nl' => 'Abdijkaas van Hastière', 'de' => 'Abtei-Käse von Hastière'],
                    'description' => [
                        'fr' => 'Fromage produit à l\'abbaye Notre-Dame d\'Hastière, disponibilité irrégulière. À demander directement à l\'abbaye.',
                        'nl' => 'Kaas geproduceerd in de abdij Onze-Lieve-Vrouw van Hastière, wisselende beschikbaarheid.',
                        'de' => 'Käse aus der Abtei Notre-Dame von Hastière, unregelmäßige Verfügbarkeit.',
                    ],
                    'meal_context' => 'local_specialty',
                    'restaurant_name' => 'Boutique de l\'Abbaye d\'Hastière',
                    'restaurant_address' => 'Abbaye Notre-Dame d\'Hastière, Hastière',
                    'price_estimate_eur' => 8.00,
                    'kcal_estimate' => 500,
                    'notes' => [
                        'fr' => 'Disponibilité irrégulière. Demander à l\'abbaye. Pour pique-nique du lendemain. 5-8 €.',
                        'nl' => 'Wisselende beschikbaarheid. Aan de abdij vragen.',
                        'de' => 'Unregelmäßige Verfügbarkeit. Bei der Abtei anfragen.',
                    ],
                ],
            ],

            // ─── BE-08 — Hastière → Givet ───────────────────────────────────
            [
                'stage_code' => 'BE-08',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Cacasse à cul nu', 'nl' => 'Cacasse à cul nu', 'de' => 'Cacasse à cul nu'],
                    'description' => [
                        'fr' => 'Plat ardennais paysan : pommes de terre + oignons + lard + eau, cuisson lente cocotte. Rassasiant après une étape chargée. Entrée en Ardenne par l\'assiette.',
                        'nl' => 'Ardens boerengerecht: aardappelen + uien + spek + water, langzaam in een kookpot gegaard.',
                        'de' => 'Ardenner Bauerngerricht: Kartoffeln + Zwiebeln + Speck + Wasser, langsam im Topf gegart.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Le P\'tit Bistrot',
                    'restaurant_address' => 'Givet, France',
                    'price_estimate_eur' => 14.00,
                    'kcal_estimate' => 1300,
                    'notes' => [
                        'fr' => 'L\'entrée en Ardenne par l\'assiette ! 14-20 €. + boudin blanc de Rethel IGP en accompagnement.',
                        'nl' => 'De culinaire inwijding in de Ardennen!',
                        'de' => 'Die kulinarische Einführung in die Ardennen!',
                    ],
                ],
            ],

            // ─── BE-09 — Givet → Doische ────────────────────────────────────
            [
                'stage_code' => 'BE-09',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Table d\'hôtes Doische (cuisine de la Fagne)', 'nl' => 'Gastenrestaurant Doische (Fagne-keuken)', 'de' => 'Gästerestaurant Doische (Fagne-Küche)'],
                    'description' => [
                        'fr' => 'Cuisine régionale de la Fagne, sur commande la veille. Produits locaux de la vallée du Viroin.',
                        'nl' => 'Regionale Fagne-keuken, dag ervoor te reserveren.',
                        'de' => 'Regionale Fagne-Küche, am Vortag zu bestellen.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_address' => 'Doische (demander au gîte)',
                    'price_estimate_eur' => 22.00,
                    'kcal_estimate' => 1100,
                    'notes' => [
                        'fr' => 'Sur commande la veille uniquement. 20-25 €.',
                        'nl' => 'Alleen op bestelling dag ervoor.',
                        'de' => 'Nur auf Vorbestellung am Vortag.',
                    ],
                ],
            ],

            // ─── BE-10 — Doische → Olloy-sur-Viroin ─────────────────────────
            [
                'stage_code' => 'BE-10',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Super des Fagnes à la Brasserie des Fagnes', 'nl' => 'Super des Fagnes bij Brasserie des Fagnes', 'de' => 'Super des Fagnes in der Brasserie des Fagnes'],
                    'description' => [
                        'fr' => 'Bière artisanale brassée sur place + plat régional. La Brasserie des Fagnes est visible depuis la salle.',
                        'nl' => 'Ter plaatse gebrouwen ambachtelijk bier + regionaal gerecht. De brouwerij is zichtbaar vanuit de eetzaal.',
                        'de' => 'Vor Ort gebrautes Handwerksbier + regionales Gericht. Die Brauerei ist vom Speisesaal aus sichtbar.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Restaurant Brasserie des Fagnes',
                    'restaurant_address' => 'Nismes, Viroinval',
                    'price_estimate_eur' => 17.00,
                    'kcal_estimate' => 1200,
                    'notes' => [
                        'fr' => 'La récompense houblonnée avant Rocroi. 15-20 €.',
                        'nl' => 'De hopbeloning voor Rocroi.',
                        'de' => 'Die Hopfenbelohnung vor Rocroi.',
                    ],
                ],
            ],

            // ─── BE-11 — Olloy → Couvin ─────────────────────────────────────
            [
                'stage_code' => 'BE-11',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Jambon d\'Ardennes IGP + Chimay trappiste', 'nl' => 'Ardense Hesp IGP + Chimay trappist', 'de' => 'Ardenner Schinken IGP + Chimay Trappistenbier'],
                    'description' => [
                        'fr' => 'Jambon fumé au bois de hêtre IGP des Ardennes. À accompagner d\'une Chimay rouge, bleue ou blanche — abbaye trappiste à 20 km de Couvin.',
                        'nl' => 'Met beukenhout gerookte Ardenner hesp IGP. Te combineren met Chimay rood, blauw of wit.',
                        'de' => 'Mit Buchenholz geräucherter Ardenner Schinken IGP. Dazu ein Chimay rot, blau oder weiß.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Le Cheval Blanc',
                    'restaurant_address' => 'Couvin',
                    'price_estimate_eur' => 18.00,
                    'kcal_estimate' => 1100,
                    'notes' => [
                        'fr' => '15-22 €. Abbaye Chimay à 20 km (excursion si jour de repos).',
                        'nl' => 'Chimay-abdij op 20 km.',
                        'de' => 'Chimay-Abtei 20 km entfernt.',
                    ],
                ],
            ],

            // ─── BE-12 — Couvin → Rocroi ────────────────────────────────────
            [
                'stage_code' => 'BE-12',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Cacasse ardennaise au Vauban', 'nl' => 'Ardense cacasse bij Le Vauban', 'de' => 'Ardenner Cacasse im Le Vauban'],
                    'description' => [
                        'fr' => 'Variante ardennaise de la cacasse à cul nu. Repas de fin de tronçon Belgique dans l\'étoile Vauban de Rocroi.',
                        'nl' => 'Ardense variant van de cacasse à cul nu. Eindmaaltijd van het Belgische traject in de Vauban-ster van Rocroi.',
                        'de' => 'Ardenner Variante der Cacasse à cul nu. Abschlussmahlzeit des belgischen Abschnitts im Vauban-Stern von Rocroi.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Le Vauban',
                    'restaurant_address' => 'Place d\'Armes, Rocroi, France',
                    'price_estimate_eur' => 18.00,
                    'kcal_estimate' => 1200,
                    'notes' => [
                        'fr' => 'Repas symbolique de fin de tronçon Belgique. 15-22 €. Journée de repos recommandée ici.',
                        'nl' => 'Symbolische eindmaaltijd van het Belgische traject.',
                        'de' => 'Symbolische Abschlussmahlzeit des belgischen Abschnitts.',
                    ],
                ],
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($meals as $item) {
            $stageId = $stagesByCode[$item['stage_code']] ?? null;

            if ($stageId === null) {
                $this->command->warn("MealSeeder : stage {$item['stage_code']} introuvable — skipping.");
                continue;
            }

            $nameFr = $item['data']['name']['fr'];

            $existing = Meal::where('stage_id', $stageId)
                ->whereJsonContains('name->fr', $nameFr)
                ->first();

            if ($existing !== null) {
                $existing->update(array_merge(['stage_id' => $stageId], $item['data']));
                $updated++;
            } else {
                Meal::create(array_merge(['stage_id' => $stageId], $item['data']));
                $created++;
            }
        }

        Log::info('MealSeeder terminé', ['created' => $created, 'updated' => $updated]);
        $this->command->info("MealSeeder : {$created} créés, {$updated} mis à jour.");
    }
}
