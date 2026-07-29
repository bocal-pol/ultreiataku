<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Meal;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Repas signatures France — les 7 incontournables gastronomiques + clés par région.
 *
 * Sources :
 *   - ravitaillement/ou-manger-local-france.md
 *   - ravitaillement/specialites-champagne-ardennes.md (Champagne)
 *   - ravitaillement/specialites-bourgogne.md (Bourgogne)
 *   - ravitaillement/specialites-berry.md (Berry)
 *   - ravitaillement/specialites-limousin.md (Limousin)
 *   - ravitaillement/specialites-perigord.md (Périgord)
 *   - ravitaillement/specialites-landes-gascogne.md (Landes)
 *   - ravitaillement/specialites-bearn-pays-basque.md (Béarn/Pays basque)
 *
 * Idempotent via whereJsonContains sur name.fr + stage_id.
 */
class MealSeederFrance extends Seeder
{
    public function run(): void
    {
        $this->command->info('MealSeederFrance — démarrage');

        $stagesByCode = Stage::pluck('id', 'code');

        $meals = [
            // ─── FR-03 R1 — Reims (Champagne) ────────────────────────────────
            [
                'stage_code' => 'FR-03',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Jambon de Reims persillé + potée champenoise', 'nl' => 'Reimsham met peterselie + champenoise stamppot', 'de' => 'Petersilienschinken aus Reims + Champenoise Eintopf'],
                    'description' => [
                        'fr' => 'Jambon de Reims : jambon blanc persillé en gelée, spécialité charcutière champenoise. Accompagné d\'une potée champenoise (chou, légumes, saucisse fumée). Dîner de repos à Reims R1.',
                        'nl' => 'Reimsham: witte ham met peterselie in gelei, champenoise specialiteit. Vergezeld van champenoise stamppot (kool, groenten, gerookte worst).',
                        'de' => 'Reimsschinken: weißer Petersilienschinken in Gelee, Champagner-Charcuterie-Spezialität. Dazu Champenoise Eintopf (Kohl, Gemüse, Räucherwurst).',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Au Petit Comptoir ou brasserie du centre',
                    'restaurant_address' => 'Reims, Marne (51)',
                    'price_estimate_eur' => 18.00,
                    'kcal_estimate' => 1200,
                    'notes' => ['fr' => 'Dîner de repos R1. 15-22 €. + Biscuit rose de Reims (Maison Fossier, 5-8 €) à prendre en boutique.', 'nl' => 'Rustdiner R1. 15-22 €. + Roze biscuit Reims (Maison Fossier) als souvenir.', 'de' => 'Ruhediner R1. 15-22 €. + Rosabiscuit Reims (Maison Fossier) als Souvenir.'],
                ],
            ],

            // ─── FR-04 J7 — Verzy (Champagne vigneronne) ─────────────────────
            [
                'stage_code' => 'FR-04',
                'data' => [
                    'meal_type' => 'snack',
                    'name' => ['fr' => 'Coupe de champagne chez un vigneron de Verzy', 'nl' => 'Champagneglas bij een wijnboer in Verzy', 'de' => 'Ein Glas Champagner bei einem Winzer in Verzy'],
                    'description' => [
                        'fr' => 'Champagne de vigneron grand cru (bien moins cher qu\'en maison de négoce). Après la boucle des Faux au matin : la récompense méritée. Verzenay + Verzy = 2 villages de grands crus de la Montagne de Reims.',
                        'nl' => 'Grand cru wijnboerschampagne (veel goedkoper dan bij handelshuis). Na de Faux-lus \'s ochtends: de verdiende beloning.',
                        'de' => 'Grand Cru Winzerchampagner (viel günstiger als beim Handelsbetrieb). Nach der Faux-Runde am Morgen: die verdiente Belohnung.',
                    ],
                    'meal_context' => 'local_specialty',
                    'restaurant_name' => 'Vigneron grand cru Verzy/Verzenay',
                    'restaurant_address' => 'Verzy ou Verzenay, Marne (51)',
                    'price_estimate_eur' => 8.00,
                    'kcal_estimate' => 100,
                    'notes' => ['fr' => '6-10 € la coupe. À consommer avec modération (étape Châlons demain). Le vrai champagne de producteur, pas la marque de supermarché.', 'nl' => '6-10 € per glas. Met mate (etappe Châlons morgen).', 'de' => '6-10 € das Glas. Mit Maß (Etappe Châlons morgen).'],
                ],
            ],

            // ─── FR-08 — Montier-en-Der (Champagne humide) ───────────────────
            [
                'stage_code' => 'FR-08',
                'data' => [
                    'meal_type' => 'lunch',
                    'name' => ['fr' => 'Rosé des Riceys + gougères', 'nl' => 'Rosé des Riceys + gougères', 'de' => 'Rosé des Riceys + Gougères'],
                    'description' => [
                        'fr' => 'Rosé des Riceys : le rosé préféré de Louis XIV, seule appellation de rosé tranquille en Champagne (AOC). Gougères : choux au fromage de Bourgogne, croustillants. Accord parfait pour un pique-nique entre les vignes.',
                        'nl' => 'Rosé des Riceys: de rosé van Lodewijk XIV, enige appelatie van stille rosé in Champagne. Gougères: kaassoesjes uit Bourgondië.',
                        'de' => 'Rosé des Riceys: der Lieblingsrosé Ludwigs XIV., einzige AOC für stillen Rosé in der Champagne. Gougères: Käseprofiteroles aus Burgund.',
                    ],
                    'meal_context' => 'grocery',
                    'restaurant_address' => 'Cave Les Riceys ou Côte des Bar (Bar-sur-Seine à J9)',
                    'price_estimate_eur' => 10.00,
                    'kcal_estimate' => 400,
                    'notes' => ['fr' => '8-12 € bouteille + 4 € gougères. Prendre dans les caves de Les Riceys (J10) ou Bar-sur-Seine (J9).', 'nl' => '8-12 € fles + 4 € gougères. Kopen in de kelders van Les Riceys.', 'de' => '8-12 € Flasche + 4 € Gougères. In den Kellern von Les Riceys kaufen.'],
                ],
            ],

            // ─── FR-15 R2 — Vézelay (Bourgogne) ──────────────────────────────
            [
                'stage_code' => 'FR-15',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Escargots de Bourgogne + époisses rôti + bœuf bourguignon', 'nl' => 'Bourgondische slakken + geroosterde époisses + boeuf bourguignon', 'de' => 'Burgundische Schnecken + gerösteter Époisses + Boeuf Bourguignon'],
                    'description' => [
                        'fr' => 'Le trio bourguignon incontournable. Escargots au beurre d\'ail (AOP). Époisses rôti (fromage AOC au lait cru, croûte lavée au marc de Bourgogne — odeur puissante, goût sublime). Bœuf bourguignon mijoté au pinot noir. Dîner de repos R2 à Vézelay.',
                        'nl' => 'Het onmisbare Bourgondische trio. Slakken in knoflookboter (AOP). Geroosterde Époisses (rauwe melk AOP kaas, gewassen schil). Boeuf bourguignon met pinot noir.',
                        'de' => 'Das unverzichtbare burgundische Trio. Schnecken in Knoblauchbutter (AOP). Gerösteter Époisses (Rohmilch-AOC-Käse, mit Marc de Bourgogne gewaschen). Boeuf Bourguignon mit Pinot Noir.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'À la Fortune du Pot ou Le Cheval Blanc',
                    'restaurant_address' => 'Vézelay, Yonne (89)',
                    'price_estimate_eur' => 23.00,
                    'kcal_estimate' => 1500,
                    'notes' => ['fr' => '⭐ Le dîner symbolique du pèlerinage. 18-28 €. Compter 1 bouteille de bourgogne rouge par table (pas de lendemain d\'étape longue le R2).', 'nl' => '⭐ Het symbolische pelgrimsavondeten. 18-28 €.', 'de' => '⭐ Das symbolische Pilgerdiner. 18-28 €.'],
                ],
            ],

            // ─── FR-17 — La Charité (Berry/Val de Loire) ──────────────────────
            [
                'stage_code' => 'FR-17',
                'data' => [
                    'meal_type' => 'lunch',
                    'name' => ['fr' => 'Crottin de Chavignol AOP + pouilly-fumé', 'nl' => 'Crottin de Chavignol AOP + pouilly-fumé', 'de' => 'Crottin de Chavignol AOP + Pouilly-fumé'],
                    'description' => [
                        'fr' => 'Crottin de Chavignol : fromage de chèvre AOP de la région de Sancerre (village à 15 km). Goût puissant selon l\'affinage. Avec un pouilly-fumé (vignoble à 10 km de La Charité) — accord région parfait. Le Sancerrois est le plus beau détour gastronomique du Berry.',
                        'nl' => 'Crottin de Chavignol: AOP geitenkaas uit de Sancerre-streek (dorp 15 km weg). Krachtige smaak afhankelijk van rijping. Met pouilly-fumé (10 km van La Charité).',
                        'de' => 'Crottin de Chavignol: AOP-Ziegenkäse aus der Sancerre-Region (15 km entfernt). Kräftiger Geschmack nach Reifegrad. Mit Pouilly-fumé (10 km von La Charité).',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_address' => 'La Charité-sur-Loire, Nièvre (58)',
                    'price_estimate_eur' => 15.00,
                    'kcal_estimate' => 600,
                    'notes' => ['fr' => '12-18 €. Fromage à prendre au marché ou caves du coin.', 'nl' => '12-18 €. Kaas op de markt of in de plaatselijke kelders.', 'de' => '12-18 €. Käse auf dem Markt oder in lokalen Kellern.'],
                ],
            ],

            // ─── FR-18 R3 — Bourges (Berry) ──────────────────────────────────
            [
                'stage_code' => 'FR-18',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Pâté berrichon aux pommes de terre + lentilles vertes du Berry', 'nl' => 'Berrichon aardappeltaart + groene linzen uit Berry', 'de' => 'Berrichon Kartoffelkuchen + grüne Linsen aus dem Berry'],
                    'description' => [
                        'fr' => 'Pâté berrichon : tarte aux pommes de terre en croûte, cousin de la truffiade du Cantal. Lentilles vertes du Berry (IGP) à l\'huile de noix. Accompagné de vin du Menetou-Salon ou Quincy (vignobles du Berry). Dîner de repos R3.',
                        'nl' => 'Berrichon taart: aardappelen in korstdeeg, neef van de Cantal-truffiade. Groene linzen uit Berry (IGP) met walnootolie.',
                        'de' => 'Berrichon Pastete: Kartoffeln im Teigmantel, Verwandter der Cantal-Truffiade. Grüne Linsen aus dem Berry (IGP) mit Walnussöl.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_address' => 'Bistrot vieille ville, Bourges, Cher (18)',
                    'price_estimate_eur' => 17.00,
                    'kcal_estimate' => 1200,
                    'notes' => ['fr' => '14-20 €. Maison Forestines : forestines (bonbon praliné depuis 1879) en boutique — souvenir de poche (8-12 €).', 'nl' => '14-20 €. Maison Forestines: forestines (praliné snoep 1879) — zaksouvenir.', 'de' => '14-20 €. Maison Forestines: Forestines (Praliné-Bonbon 1879) — Taschenandenken.'],
                ],
            ],

            // ─── FR-26 R4 — Limoges (Limousin) ───────────────────────────────
            [
                'stage_code' => 'FR-26',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Boudin aux châtaignes + clafoutis aux cerises (Limoges)', 'nl' => 'Bloedworst met kastanjes + clafoutis met kersen (Limoges)', 'de' => 'Blutwurst mit Kastanien + Clafoutis mit Kirschen (Limoges)'],
                    'description' => [
                        'fr' => 'Boudin aux châtaignes : spécialité limousine — boudin noir parfumé aux châtaignes du Limousin. Clafoutis aux cerises bigarreaux : le dessert roi du Limousin, en juin = pleine saison. Halles centrales de Limoges pour le meilleur approvisionnement.',
                        'nl' => 'Bloedworst met kastanjes: Limousijnse specialiteit. Clafoutis met kriekkers: het koninklijke dessert van het Limousin, in juni = hoogseizoen.',
                        'de' => 'Blutwurst mit Kastanien: Limousinische Spezialität. Clafoutis mit Sauerkirschen: das königliche Dessert des Limousin, im Juni = Hochsaison.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Halles centrales ou bistrot quartier Boucherie',
                    'restaurant_address' => 'Limoges, Haute-Vienne (87)',
                    'price_estimate_eur' => 18.00,
                    'kcal_estimate' => 1300,
                    'notes' => ['fr' => '14-22 €. Halles centrales ouvertes tous les jours ⭐⭐ — meilleure adresse de Limoges côté produits frais.', 'nl' => '14-22 €. Halles centrales elke dag open ⭐⭐ — beste adres Limoges voor verse producten.', 'de' => '14-22 €. Halles centrales täglich offen ⭐⭐ — beste Adresse Limoges für Frischprodukte.'],
                ],
            ],

            // ─── FR-29 R5 — Périgueux (Périgord) ─────────────────────────────
            [
                'stage_code' => 'FR-29',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Magret de canard + pommes de terre sarladaises + cabécou', 'nl' => 'Eendenborst + sarladaise aardappelen + cabécou', 'de' => 'Entenmagret + Sarladaise Kartoffeln + Cabécou'],
                    'description' => [
                        'fr' => 'La séquence gastronomique la plus dense du voyage. Magret de canard du Périgord, poêlé rosé. Pommes de terre sarladaises : cuites à la graisse de canard avec ail et persil (les meilleures du monde). Cabécou du Périgord : fromage de chèvre frais ou demi-sec. Accompagnement de noix du Périgord.',
                        'nl' => 'De gastronomisch dichtste sequentie van de reis. Périgord-eendenborst, rosé gebakken. Sarladaise aardappelen: gekookt in eendenvet met knoflook en peterselie. Cabécou: verse of halfdroge geitenkaas.',
                        'de' => 'Die gastronomisch dichteste Sequenz der Reise. Périgord-Entenmagret, rosé gebraten. Sarladaise Kartoffeln: in Entenfett mit Knoblauch und Petersilie (die besten der Welt). Cabécou: frischer oder halbtrockener Ziegenkäse.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Restaurant place du Coderc',
                    'restaurant_address' => 'Périgueux, Dordogne (24)',
                    'price_estimate_eur' => 21.00,
                    'kcal_estimate' => 1500,
                    'notes' => ['fr' => '⭐ Le sommet gastronomique du pèlerinage. 16-26 €. Marché place du Coderc : mercredi ET samedi matin (foie gras en saison, truffe en hiver). Conserves artisanales de foie gras (souvenir transportable).', 'nl' => '⭐ Het gastronomische hoogtepunt van de pelgrimstocht. 16-26 €. Markt place du Coderc: wo EN za.', 'de' => '⭐ Der gastronomische Höhepunkt der Pilgerreise. 16-26 €. Markt Place du Coderc: Mi UND Sa.'],
                ],
            ],

            // ─── FR-30 — Sainte-Foy-la-Grande (Dordogne) ─────────────────────
            [
                'stage_code' => 'FR-30',
                'data' => [
                    'meal_type' => 'lunch',
                    'name' => ['fr' => 'Marché du samedi Sainte-Foy-la-Grande — pique-nique de roi', 'nl' => 'Zaterdagmarkt Sainte-Foy-la-Grande — koninklijke picknick', 'de' => 'Samstagmarkt Sainte-Foy-la-Grande — königliches Picknick'],
                    'description' => [
                        'fr' => 'Un des plus beaux marchés de France ⭐⭐. Bastide du XIIIe siècle sur la Dordogne. Produits locaux : foie gras, Bergerac, fromages, charcuteries, pain de campagne. À caler l\'étape un samedi si possible. Budget pique-nique : 8-15 €.',
                        'nl' => 'Een van de mooiste markten van Frankrijk ⭐⭐. Bastide 13e eeuw aan de Dordogne. Lokale producten: ganzenleverkoeien, Bergerac, kazen, vleeswaren, boerenbrood.',
                        'de' => 'Einer der schönsten Märkte Frankreichs ⭐⭐. Bastide 13. Jh. an der Dordogne. Lokale Produkte: Gänsestopfleber, Bergerac, Käse, Charcuterie, Landbrot.',
                    ],
                    'meal_context' => 'grocery',
                    'restaurant_address' => 'Place centrale, Sainte-Foy-la-Grande, Gironde (33)',
                    'price_estimate_eur' => 12.00,
                    'kcal_estimate' => 900,
                    'notes' => ['fr' => '⭐ Caler l\'étape un samedi si possible — le marché se termine avant 12h30. 8-15 € pour un pique-nique roi.', 'nl' => '⭐ Etappe op zaterdag inplannen indien mogelijk. Markt eindigt voor 12h30.', 'de' => '⭐ Etappe möglichst auf Samstag legen. Markt endet vor 12h30.'],
                ],
            ],

            // ─── FR-32 — Bazas (Bazadais) ────────────────────────────────────
            [
                'stage_code' => 'FR-32',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Bœuf de Bazas AOC grillé aux sarments', 'nl' => 'Bazas-rund AOC gegrild op wijnranken', 'de' => 'Boeuf de Bazas AOC gegrillt auf Weinreben'],
                    'description' => [
                        'fr' => 'Entrecôte de bœuf de Bazas (race locale AOC, élevage extensif, persillage exceptionnel) grillée aux sarments de vigne. L\'une des meilleures viandes de France. Accompagnement : pommes de terre à la landaise.',
                        'nl' => 'Entrecôte van Bazas-rund (lokaal AOC-ras, uitgebreide teelt, uitzonderlijke vetmarmering) gegrild op wijnranken. Een van de beste runderen van Frankrijk.',
                        'de' => 'Entrecôte vom Bazas-Rind (lokale AOC-Rasse, extensive Haltung, außergewöhnliche Marmorierung) gegrillt auf Weinreben. Eines der besten Fleische Frankreichs.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_address' => 'Bazas, Gironde (33) — table locale au centre',
                    'price_estimate_eur' => 22.00,
                    'kcal_estimate' => 1400,
                    'notes' => ['fr' => '⭐ Un des 7 incontournables gustatifs. 18-26 €. Dernier grand repas avant les Landes. Après Bazas : autonomie alimentaire sur 2-3 jours.', 'nl' => '⭐ Een van de 7 culinaire hoogtepunten. 18-26 €. Laatste grote maaltijd voor de Landes.', 'de' => '⭐ Einer der 7 kulinarischen Höhepunkte. 18-26 €. Letzte große Mahlzeit vor den Landes.'],
                ],
            ],

            // ─── FR-35 — Saint-Sever (Chalosse/Landes) ───────────────────────
            [
                'stage_code' => 'FR-35',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Garbure landaise + pastis landais', 'nl' => 'Landse garbure + pastis landais', 'de' => 'Garbure landaise + Pastis landais'],
                    'description' => [
                        'fr' => 'Garbure landaise : soupe-repas au confit de canard, haricots, légumes d\'hiver, chou. Plat paysan des Landes et de Gascogne, consistant et chaud. Pastis landais en dessert : brioche parfumée à l\'anis et au rhum, spécialité des Landes.',
                        'nl' => 'Landse garbure: maaltijdsoep met eendenconfit, bonen, wintergroenten, kool. Pastis landais: geanijste brioche met rum.',
                        'de' => 'Landaise Garbure: Mahlzeitsuppe mit Entenconfit, Bohnen, Wintergemüse, Kohl. Pastis Landais: Anis-Rum-Brioche als Dessert.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_address' => 'Mont-de-Marsan ou Saint-Sever, Landes (40)',
                    'price_estimate_eur' => 17.00,
                    'kcal_estimate' => 1300,
                    'notes' => ['fr' => '14-20 €. L\'Armagnac en digestif (une larme, jour de repos). Plat idéal pour recharger avant la Chalosse.', 'nl' => '14-20 €. Armagnac als digestief (een druppel, rustdag).', 'de' => '14-20 €. Armagnac als Digestif (ein Tropfen, Ruhetag).'],
                ],
            ],

            // ─── FR-37 — Sauveterre-de-Béarn (Béarn) ────────────────────────
            [
                'stage_code' => 'FR-37',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Garbure béarnaise + fromage Ossau-Iraty AOP', 'nl' => 'Béarnse garbure + Ossau-Iraty AOP kaas', 'de' => 'Béarnaise Garbure + Ossau-Iraty AOP Käse'],
                    'description' => [
                        'fr' => 'Garbure béarnaise : version béarnaise de la soupe-repas (jambon de Bayonne à la place du confit landais, fèves). Ossau-Iraty AOP : fromage de brebis basque/béarnais (pâte ferme, croûte naturelle, goût de noisette). L\'Ossau-Iraty accompagné de confiture de cerises noires de Bayonne = accord parfait.',
                        'nl' => 'Béarnse garbure: Béarnse versie van de maaltijdsoep (Bayonne-ham i.p.v. landse confit). Ossau-Iraty AOP: Baskische/Béarnse schapenkaas (harde pasta, notensmaak) + zwarte kersenconfituur.',
                        'de' => 'Béarnaise Garbure: Béarnaise Version der Mahlzeitsuppe (Bayonner Schinken statt Landaiser Confit). Ossau-Iraty AOP: Baskischer/Béarnischer Schafskäse + Schwarzkirschkonfitüre.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_address' => 'Orthez ou Sauveterre-de-Béarn, Pyrénées-Atlantiques (64)',
                    'price_estimate_eur' => 17.00,
                    'kcal_estimate' => 1200,
                    'notes' => ['fr' => '14-20 €. Truite du gave aussi disponible à Sauveterre (gave d\'Oloron tout proche). Le paysage devient pyrénéen — le Béarn culinaire annonce les Pyrénées.', 'nl' => '14-20 €. Forel van de gave ook beschikbaar in Sauveterre.', 'de' => '14-20 €. Forelle der Gave auch in Sauveterre verfügbar.'],
                ],
            ],

            // ─── FR-40 R7-R8 — SJPP (Pays basque) ────────────────────────────
            [
                'stage_code' => 'FR-40',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Piperade + gâteau basque + Irouléguy (SJPP)', 'nl' => 'Piperade + Baskische cake + Irouléguy (SJPP)', 'de' => 'Piperade + Baskischer Kuchen + Irouléguy (SJPP)'],
                    'description' => [
                        'fr' => 'Piperade : œufs brouillés aux poivrons, tomates et piment d\'Espelette — le plat emblématique du Pays basque. Gâteau basque à la cerise noire (Itxassou) ou crème patissière. Irouléguy rouge ou blanc : le seul vignoble AOC du Pays basque français (les vignes autour de SJPP). Brebis-confiture cerise noire en dessert.',
                        'nl' => 'Piperade: roerei met paprika, tomaten en Espelette-peper. Baskische cake met zwarte kersen of banketbakkersroom. Irouléguy: enige AOC-wijngaard van het Franse Baskenland.',
                        'de' => 'Piperade: Rührei mit Paprika, Tomaten und Espelette-Pfeffer. Baskischer Kuchen mit Schwarzkirschen oder Konditorcreme. Irouléguy: einziger AOC-Weinberg des französischen Baskenlandes.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Restaurant rue de la Citadelle',
                    'restaurant_address' => 'Saint-Jean-Pied-de-Port, Pyrénées-Atlantiques (64)',
                    'price_estimate_eur' => 21.00,
                    'kcal_estimate' => 1400,
                    'notes' => ['fr' => '⭐ La récompense avant les Pyrénées. 16-26 €. Marché lundi ⭐ (recharge complète : Compeed, gaz, chaussettes + ravitaillement pour J1 Espagne). Axoa de veau (émincé au piment d\'Espelette) en alternative à la piperade.', 'nl' => '⭐ De beloning voor de Pyreneeën. 16-26 €. Maandagmarkt ⭐ (volledige aanvulling).', 'de' => '⭐ Die Belohnung vor den Pyrenäen. 16-26 €. Montagsmarkt ⭐ (vollständige Aufstockung).'],
                ],
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($meals as $item) {
            $stageId = $stagesByCode[$item['stage_code']] ?? null;

            if ($stageId === null) {
                $this->command->warn("MealSeederFrance : stage {$item['stage_code']} introuvable — skipping.");

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

        Log::info('MealSeederFrance terminé', ['created' => $created, 'updated' => $updated]);
        $this->command->info("MealSeederFrance : {$created} créés, {$updated} mis à jour.");
    }
}
