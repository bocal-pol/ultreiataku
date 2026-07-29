<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Database\Seeders;

use App\Modules\Pilgrimage\Models\Meal;
use App\Modules\Pilgrimage\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

/**
 * Repas signatures Espagne (Camino del Norte + Module Picos).
 *
 * Sources :
 *   - ravitaillement/carnet-espagne.md
 *   - ravitaillement/ou-manger-local-espagne.md
 *   - ravitaillement/specialites-euskadi.md
 *   - ravitaillement/specialites-cantabrie.md
 *   - ravitaillement/specialites-asturies.md
 *   - ravitaillement/specialites-galice.md
 *
 * Idempotent via whereJsonContains sur name.fr + stage_id.
 */
class MealSeederEspagne extends Seeder
{
    public function run(): void
    {
        $this->command->info('MealSeederEspagne — démarrage');

        $stagesByCode = Stage::pluck('id', 'code');

        $meals = [

            // ── ES-04 Irun — premiers pintxos espagnols ──────────────────────
            [
                'stage_code' => 'ES-04',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Pintxos Irun (Bar Juanito)', 'nl' => 'Pintxos Irun (Bar Juanito)', 'de' => 'Pintxos Irun (Bar Juanito)'],
                    'description' => [
                        'fr' => 'Les premiers pintxos espagnols ! Petites tartines sur pain de baguette avec garnitures variées : tortilla, anchois, crevette, jamón. Se commandent en pointant derrière le bar. Payer au départ. Ambiance txikiteo (tournée des bars).',
                        'nl' => 'De eerste Spaanse pintxos! Kleine tartines op stokbrood met diverse garnituren. Bestellen door te wijzen achter de bar. Betalen bij vertrek. Txikiteo-sfeer (barterras ronde).',
                        'de' => 'Die ersten spanischen Pintxos! Kleine Tartinen auf Baguette mit verschiedenen Belägen. Bestellen durch Zeigen hinter der Bar. Beim Weggehen bezahlen. Txikiteo-Atmosphäre.',
                    ],
                    'meal_context' => 'local_specialty',
                    'price_estimate_eur' => 12.00,
                    'kcal_estimate' => 600,
                    'notes' => [
                        'fr' => 'Budget : 1-2 € le pintxo + 2 € la txakoli. Prévoir 10-15 € pour une belle tournée. On ne commande pas une assiette — on pointe, on mange, on pointe encore.',
                        'nl' => 'Budget: 1-2 € per pintxo + 2 € txakoli. Voorzien 10-15 € voor een mooie ronde.',
                        'de' => 'Budget: 1-2 € pro Pintxo + 2 € Txakoli. Ca. 10-15 € für eine schöne Runde einplanen.',
                    ],
                ],
            ],

            // ── ES-05 San Sebastián — pintxos capitale mondiale ──────────────
            [
                'stage_code' => 'ES-05',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Pintxos Parte Vieja San Sebastián', 'nl' => 'Pintxos Parte Vieja San Sebastián', 'de' => 'Pintxos Parte Vieja San Sebastián'],
                    'description' => [
                        'fr' => 'La Mecque mondiale du pintxo. Rue 31 de Agosto et alentours : Bar La Cuchara de San Telmo (pintxos chauds créatifs), Bar Tamboril (anchois + olive + pimiento), Bar Zeruko (pintxos dégustation). Txakoli servi à bout de bras.',
                        'nl' => 'De wereldhoofdstad van de pintxo. Calle 31 de Agosto en omgeving. Txakoli geserveerd vanuit armhoogte.',
                        'de' => 'Die Welthauptstadt des Pintxo. Calle 31 de Agosto und Umgebung. Txakoli aus Armhöhe serviert.',
                    ],
                    'meal_context' => 'local_specialty',
                    'restaurant_address' => 'Calle 31 de Agosto, Parte Vieja, San Sebastián',
                    'price_estimate_eur' => 20.00,
                    'kcal_estimate' => 800,
                    'notes' => [
                        'fr' => '⭐ Incontournable. Heure d\'or : 19h-21h (bars les plus animés). Budget 20-30 € pour une belle tournée (5-8 bars). Se faire servir lentement, observer les locaux.',
                        'nl' => '⭐ Onmisbaar. Gouden uur: 19-21u (drukste bars). Budget 20-30 € voor een mooie ronde.',
                        'de' => '⭐ Unverzichtbar. Goldene Stunde: 19-21 Uhr. Budget 20-30 € für eine schöne Runde.',
                    ],
                ],
            ],

            // ── ES-07 Deba — flysch et poisson grillé ────────────────────────
            [
                'stage_code' => 'ES-07',
                'data' => [
                    'meal_type' => 'lunch',
                    'name' => ['fr' => 'Menú del día vue flysch (Deba)', 'nl' => 'Menú del día met flysch-uitzicht (Deba)', 'de' => 'Menú del día mit Flysch-Blick (Deba)'],
                    'description' => [
                        'fr' => 'Menú del día en terrasse face aux falaises du flysch de Zumaia. Primero : menestra (légumes mijotés) ou purée de légumes. Segundo : poisson grillé de la Cantábrica (merluza, besugo, bonito). Postre : flan ou tarta casera. 10-14 €.',
                        'nl' => 'Menú del día op terras voor de flysch-kliffen. Primero: menestra of groentepuree. Segundo: gegrilde vis (merluza, besugo, bonito). Postre: flan of tarta. 10-14 €.',
                        'de' => 'Menú del día auf Terrasse vor den Flysch-Kliffen. Primero: Menestra oder Gemüsepüree. Segundo: gegrillter Fisch. Postre: Flan oder Törtchen. 10-14 €.',
                    ],
                    'meal_context' => 'restaurant',
                    'price_estimate_eur' => 13.00,
                    'kcal_estimate' => 900,
                    'notes' => [
                        'fr' => 'Demander la terrasse pour voir les falaises. Le menú del día (disponible seulement 13h-15h) est LA manière économique de manger en Espagne.',
                        'nl' => 'Terras vragen voor uitzicht op de kliffen. Het menú del día (alleen 13-15u) is DE economische manier om te eten in Spanje.',
                        'de' => 'Terrasse verlangen für Kliffblick. Das Menú del día (nur 13-15 Uhr) ist DIE wirtschaftliche Art zu essen in Spanien.',
                    ],
                ],
            ],

            // ── ES-10 Bilbao — bacalao al pil-pil ───────────────────────────
            [
                'stage_code' => 'ES-10',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Bacalao al pil-pil (Bilbao)', 'nl' => 'Bacalao al pil-pil (Bilbao)', 'de' => 'Bacalao al pil-pil (Bilbao)'],
                    'description' => [
                        'fr' => 'Plat emblématique basque : morue dessalée pochée dans huile d\'olive + ail, émulsionnée lentement (le « pil-pil » = bruit de l\'huile qui bulle). Texture crémeuse sans crème. Servi avec pimientos verts frits. La recette exige une technique parfaite — ne vaut que dans un vrai restaurant basque.',
                        'nl' => 'Emblematisch Baskisch gerecht: gedesalzelde kabeljauw gepocheerd in olijfolie + knoflook, langzaam geëmulgeerd (het « pil-pil » = borrelen). Romige textuur zonder room.',
                        'de' => 'Emblematisches baskisches Gericht: entsalzter Kabeljau in Olivenöl + Knoblauch pochiert, langsam emulgiert (das « Pil-Pil » = Blubbern). Cremige Textur ohne Sahne.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'Restaurante Gure Toki (Casco Viejo)',
                    'restaurant_address' => 'Plaza Nueva, Bilbao',
                    'price_estimate_eur' => 22.00,
                    'kcal_estimate' => 650,
                    'notes' => [
                        'fr' => 'Le bacalao al pil-pil se mange le soir (jamais le midi, trop lourd avant la marche). Plaza Nueva : plusieurs options. Budget 20-28 € pour le plat + txakoli.',
                        'nl' => 'Bacalao al pil-pil \'s avonds eten (nooit \'s middags, te zwaar vóór het wandelen). Plaza Nueva: meerdere opties.',
                        'de' => 'Bacalao al pil-pil abends essen (nie mittags). Plaza Nueva: mehrere Optionen.',
                    ],
                ],
            ],

            // ── ES-11 Portuguesa — pont transbordeur, menú ouvrier ──────────
            [
                'stage_code' => 'ES-11',
                'data' => [
                    'meal_type' => 'lunch',
                    'name' => ['fr' => 'Menú del día ouvrier (Portugalete)', 'nl' => 'Arbeiders menú del día (Portugalete)', 'de' => 'Arbeiter-Menú del día (Portugalete)'],
                    'description' => [
                        'fr' => 'Menú ouvrier authentique dans les tavernes portuaires de Portugalete, héritières des traditions sidérurgiques de Bilbao. Portions XXL. Potage de légumes + bistec a la plancha + dessert casier. 9-12 €. Pain, vin ou agua inclus.',
                        'nl' => 'Authentieke arbeidersmenu in de haven-tavernes van Portugalete. Portionen XXL. 9-12 €. Brood, wijn of water inbegrepen.',
                        'de' => 'Authentische Arbeiter-Menú in den Hafentavernen von Portugalete. XXL-Portionen. 9-12 €. Brot, Wein oder Wasser inklusive.',
                    ],
                    'meal_context' => 'restaurant',
                    'price_estimate_eur' => 11.00,
                    'kcal_estimate' => 1100,
                    'notes' => [
                        'fr' => 'Ces tavernes nourrissent les ouvriers depuis 100 ans. Atmosphère authentique, prix imbattables. Après la nacelle (0,50 €), déjeuner avant de repartir.',
                        'nl' => 'Deze tavernes voeden arbeiders al 100 jaar. Authentieke sfeer, onklopbare prijzen.',
                        'de' => 'Diese Tavernen ernähren Arbeiter seit 100 Jahren. Authentische Atmosphäre.',
                    ],
                ],
            ],

            // ── ES-14 Santoña — anchois légendaires ─────────────────────────
            [
                'stage_code' => 'ES-14',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Anchois de Santoña au sel (El Capricho ou La Bodega)', 'nl' => 'Santoña-ansjovis op zout (El Capricho of La Bodega)', 'de' => 'Santoña-Sardellen in Salz (El Capricho oder La Bodega)'],
                    'description' => [
                        'fr' => 'Les anchois de Santoña sont les meilleurs d\'Espagne (IGP candidate). Conserve à l\'huile d\'olive depuis des générations de femmes anchovières. Sur pain grillé + beurre ou directement avec des olives. Accompagnés de txakoli blanc ou albariño. La récompense après 763 marches du Faro del Caballo.',
                        'nl' => 'De ansjovis van Santoña zijn de beste van Spanje (IGP-kandidaat). In olijfolie geconserveerd door generaties vrouwen. Op geroosterd brood + boter of direct met olijven. Met txakoli of albariño. De beloning na 763 treden van de Faro del Caballo.',
                        'de' => 'Die Sardellen von Santoña sind die besten Spaniens (IGP-Kandidat). In Olivenöl eingelegt. Auf Toastbrot + Butter oder direkt mit Oliven. Mit Txakoli oder Albariño. Die Belohnung nach 763 Stufen.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'El Capricho de Santoña',
                    'restaurant_address' => 'Santoña, Cantabria',
                    'price_estimate_eur' => 18.00,
                    'kcal_estimate' => 550,
                    'notes' => [
                        'fr' => '⭐ Incontournable. Demander les « anchoas de primera » (la meilleure gamme). Les conserves locales à emporter en souvenir (Conservas Serrats ou Real Conservera). 15-25 € selon gamme.',
                        'nl' => '⭐ Onmisbaar. Vraag naar « anchoas de primera ». Lokale conserven mee als souvenir.',
                        'de' => '⭐ Unverzichtbar. Nach « anchoas de primera » fragen. Lokale Konserven als Andenken mitnehmen.',
                    ],
                ],
            ],

            // ── ES-16 Santander — rabas ──────────────────────────────────────
            [
                'stage_code' => 'ES-16',
                'data' => [
                    'meal_type' => 'lunch',
                    'name' => ['fr' => 'Rabas de Santander (Taberna El Puerto)', 'nl' => 'Rabas van Santander (Taberna El Puerto)', 'de' => 'Rabas von Santander (Taberna El Puerto)'],
                    'description' => [
                        'fr' => 'Calamars frits cantabres — anneaux de seiche enfarinés et frits à l\'huile d\'olive. L\'apéro dominical de toute la Cantabrie. Servis avec limon et albariño frío. Les vraies rabas n\'ont pas de panure — juste la farine.',
                        'nl' => 'Cantabrische gebakken pijlinktvis — ringen van sepia in bloem en gefrittuurd in olijfolie. Het zondagse aperitief van heel Cantabrië. Geserveerd met citroen en koude albariño.',
                        'de' => 'Kantabrische gebratene Tintenfischringe — in Mehl gewendet und in Olivenöl frittiert. Das sonntagliche Aperitif ganz Kantabriens. Mit Zitrone und kaltem Albariño.',
                    ],
                    'meal_context' => 'local_specialty',
                    'restaurant_name' => 'Taberna El Puerto',
                    'restaurant_address' => 'Puerto de Santander',
                    'price_estimate_eur' => 10.00,
                    'kcal_estimate' => 600,
                    'notes' => [
                        'fr' => 'Les rabas se mangent debout au bar, avec un verre d\'albariño ou de Estrella. Portion : 6-10 €. L\'apéro parfait avant de trouver la pension.',
                        'nl' => 'Rabas eet men staand aan de bar, met een glas albariño. Portie: 6-10 €.',
                        'de' => 'Rabas isst man stehend an der Bar, mit einem Glas Albariño. Portion: 6-10 €.',
                    ],
                ],
            ],

            // ── ES-18 Santillana del Mar — cocido montañés ──────────────────
            [
                'stage_code' => 'ES-18',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Cocido montañés (Santillana del Mar)', 'nl' => 'Cocido montañés (Santillana del Mar)', 'de' => 'Cocido montañés (Santillana del Mar)'],
                    'description' => [
                        'fr' => 'La marmite de montagne cantabre : haricots blancs locaux + chorizo + morcilla (boudin noir) + côtes de porc + berza (chou frisé vert). Cuisson lente, portée généreuse. L\'équivalent cantabre de notre waterzooi. Le plat de la mémoire familiale — les grands-parents le faisaient ainsi.',
                        'nl' => 'De Cantabrische bergpot: witte bonen + chorizo + morcilla (bloedworst) + varkensribbetjes + berza (groene koolwilted). Trage bereiding, royale portie. De Cantabrische versie van onze waterzooi. Het gerecht van de familieherinnering.',
                        'de' => 'Der kantabrische Bergeintopf: weiße Bohnen + Chorizo + Morcilla (Blutwurst) + Schweinerippchen + Berza (grüner Wirsingkohl). Langsames Kochen, großzügige Portion. Das Familienerinnerungsgericht.',
                    ],
                    'meal_context' => 'restaurant',
                    'price_estimate_eur' => 15.00,
                    'kcal_estimate' => 1200,
                    'notes' => [
                        'fr' => '⭐ Ne jamais refuser un cocido montañés à Santillana. C\'est le plat de la Cantabrie. Servir avec un verre de rouge local (DO Cantabria). Ce sera lourd — à manger tôt le soir pour bien dormir avant le repos R3.',
                        'nl' => '⭐ Nooit een cocido montañés weigeren in Santillana. Met een glas rode lokale wijn. Vroeg eten voor een goede nacht voor rustdag R3.',
                        'de' => '⭐ Nie einen Cocido montañés in Santillana ablehnen. Mit einem Glas Rotwein. Früh essen vor dem Ruhetag R3.',
                    ],
                ],
            ],

            // ── ES-22 Llanes — premier culín de sidra ────────────────────────
            [
                'stage_code' => 'ES-22',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Premier culín de sidra (Llanes)', 'nl' => 'Eerste culín sidra (Llanes)', 'de' => 'Erster Culín Sidra (Llanes)'],
                    'description' => [
                        'fr' => 'La sidra asturiana : cidre naturel non filtré, pétillance naturelle, servi à bout de bras (escanciado) en très petit verre (culín). Le bruit de l\'écume contre le verre + le jet + la précision = un art. Le reste tombe à terre (normal). Accompagnée de chorizo + morcilla + cabrales. Premier culín de sidra du voyage.',
                        'nl' => 'Asturische sidra: ongefiltreerde natuurcider, met armen geschonken (escanciado) in een klein glas (culín). Het geluid van het schuim + de straal + de precisie = een kunst. De rest valt op de grond (normaal). Met chorizo + morcilla + cabrales.',
                        'de' => 'Asturische Sidra: ungefilterter Naturwein, aus Armhöhe eingeschenkt (Escanciado) in ein kleines Glas (Culín). Der Klang des Schaums + der Strahl + die Präzision = eine Kunst. Der Rest fällt auf den Boden (normal).',
                    ],
                    'meal_context' => 'local_specialty',
                    'price_estimate_eur' => 12.00,
                    'kcal_estimate' => 700,
                    'notes' => [
                        'fr' => '⭐ Rituel initiatique asturien. Une bouteille de sidra (~2,5 €) contient 8-10 culines. La règle : boire d\'un trait, jeter le fond (pour le prochain culín). Manger du chorizo a la sidra entre les culinas.',
                        'nl' => '⭐ Asturiaans inwijdingsritueel. Een fles sidra (~2,5 €) bevat 8-10 culinas. Regel: in één teug opdrinken, grond weggooien. Chorizo a la sidra eten tussen de culinas.',
                        'de' => '⭐ Asturisches Einweihungsritual. Eine Flasche Sidra (~2,5 €) enthält 8-10 Culinés. Regel: in einem Zug trinken, Rest wegwerfen.',
                    ],
                ],
            ],

            // ── ES-24 Ribadesella — fabada asturiana ─────────────────────────
            [
                'stage_code' => 'ES-24',
                'data' => [
                    'meal_type' => 'lunch',
                    'name' => ['fr' => 'Fabada asturiana (Ribadesella)', 'nl' => 'Fabada asturiana (Ribadesella)', 'de' => 'Fabada asturiana (Ribadesella)'],
                    'description' => [
                        'fr' => 'LE plat national des Asturies : fabes de la granja (gros haricots blancs locaux IGP) + compango (chorizo, morcilla, lacón — jambon salé). Cuisson 3-4 heures minimum. Texture crémeuse, bouillon rosé. Jamais de légumes verts — c\'est la règle. Servie dans une cazuela de terre cuite. Arroz con leche en dessert (incontournable).',
                        'nl' => 'HET nationale gerecht van Asturië: fabes de la granja (grote witte bonen IGP) + compango (chorizo, morcilla, lacón). 3-4 uur koken. Romige textuur, roze bouillon. Arroz con leche als dessert.',
                        'de' => 'DAS Nationalgericht Asturiens: Fabes de la granja (große weiße Bohnen IGP) + Compango (Chorizo, Morcilla, Lacón). 3-4 Stunden Kochzeit. Cremige Textur, rosafarbene Brühe. Arroz con leche als Nachtisch.',
                    ],
                    'meal_context' => 'restaurant',
                    'price_estimate_eur' => 14.00,
                    'kcal_estimate' => 1100,
                    'notes' => [
                        'fr' => '⭐ Ne jamais commander une fabada le soir (trop lourd avant la marche du lendemain). Toujours à midi. Les meilleurs à Ribadesella : Casa Arbidel ou la Sidrería del Puerto. Après la fabada : obligatoirement une sieste.',
                        'nl' => '⭐ Nooit fabada \'s avonds bestellen. Altijd \'s middags. Na de fabada: een dutje is verplicht.',
                        'de' => '⭐ Nie Fabada abends bestellen. Immer mittags. Nach der Fabada: ein Mittagsschlaf ist Pflicht.',
                    ],
                ],
            ],

            // ── ES-31 Ribadeo — premier pulpo á feira ───────────────────────
            [
                'stage_code' => 'ES-31',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Pulpo á feira (Ribadeo — entrée en Galice)', 'nl' => 'Pulpo á feira (Ribadeo — ingang Galicië)', 'de' => 'Pulpo á feira (Ribadeo — Eingang Galicien)'],
                    'description' => [
                        'fr' => 'Premier pulpo á feira en Galice ! Poulpe cuit à la perfection, coupé en rondelles sur planche de bois, saupoudré de pimentón de la Vera (fumé ou doux), sel marin gros, filet d\'huile d\'olive extra. Servi tiède. La combinaison parfaite : octopus + fumée + sel + huile.',
                        'nl' => 'Eerste pulpo á feira in Galicië! Poulpe perfect gekookt, in rondellen gesneden op een houten plank, bestrooid met pimentón de la Vera (gerookt of zoet), zeezout, olijfolie. Lauw geserveerd.',
                        'de' => 'Erster Pulpo á feira in Galicien! Perfekt gegarter Oktopus, in Scheiben auf Holzbrett, bestäubt mit Pimentón de la Vera (geräuchert oder süß), grobem Meersalz, Olivenöl. Lauwarm serviert.',
                    ],
                    'meal_context' => 'local_specialty',
                    'price_estimate_eur' => 16.00,
                    'kcal_estimate' => 500,
                    'notes' => [
                        'fr' => '⭐ Le moment clé de l\'entrée en Galice. Le pulpo se mange avec cure-dents (palillos). Demander el pimentón picante si on aime épicé. Accompagner d\'un verre de Ribeiro blanc (1,50-2 €) ou d\'une taza de barro (bol en terre cuite — tradition).',
                        'nl' => '⭐ Het sleutelmoment van de ingang in Galicië. Pulpo eten met tandenstokers. Pimentón picante vragen als men van pittig houdt. Met Ribeiro blanc of een taza de barro.',
                        'de' => '⭐ Das Schlüsselmoment des Eingangs in Galicien. Mit Zahnstochern essen. Pimentón picante bitten wenn man es scharf mag. Mit Ribeiro blanco oder einer Taza de barro.',
                    ],
                ],
            ],

            // ── ES-37 Arzúa — queixo Arzúa-Ulloa ───────────────────────────
            [
                'stage_code' => 'ES-37',
                'data' => [
                    'meal_type' => 'lunch',
                    'name' => ['fr' => 'Queixo Arzúa-Ulloa (AOP) + empanada (Arzúa)', 'nl' => 'Queixo Arzúa-Ulloa (AOP) + empanada (Arzúa)', 'de' => 'Queixo Arzúa-Ulloa (AOP) + Empanada (Arzúa)'],
                    'description' => [
                        'fr' => 'Queixo Arzúa-Ulloa : fromage galicien AOP, pâte molle crémeuse, saveur douce et lactique. Le manger là où il naît (Arzúa est son berceau). Accompagné d\'empanada gallega (chausson feuilleté farci : thon ou poulet ou xoubas/sardines). Miel de la ría en dessert.',
                        'nl' => 'Queixo Arzúa-Ulloa: Galicische AOP-kaas, zachte romige pasta. Eten waar hij geboren wordt (Arzúa is zijn wieg). Met empanada gallega (gevuld deegkorstje: tonijn of kip of sardines).',
                        'de' => 'Queixo Arzúa-Ulloa: Galicischer AOP-Käse, weiche cremige Paste. Dort essen wo er entsteht (Arzúa ist seine Wiege). Mit Empanada gallega (gefülltes Blätterteig-Gebäck).',
                    ],
                    'meal_context' => 'local_specialty',
                    'price_estimate_eur' => 10.00,
                    'kcal_estimate' => 700,
                    'notes' => [
                        'fr' => 'Le marché hebdomadaire d\'Arzúa (Mercado de Arzúa) est célèbre pour le fromage. Acheter un morceau pour la dernière étape jusqu\'à Santiago (les fromagers y taillent des portions-pèlerin).',
                        'nl' => 'De weekmarkt van Arzúa is beroemd om zijn kaas. Een stuk kopen voor de laatste etappe naar Santiago.',
                        'de' => 'Der Wochenmarkt von Arzúa ist berühmt für seinen Käse. Ein Stück für die letzte Etappe nach Santiago kaufen.',
                    ],
                ],
            ],

            // ── ES-39 Santiago — mariscada de célébration ───────────────────
            [
                'stage_code' => 'ES-39',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Mariscada de célébration (Santiago de Compostela)', 'nl' => 'Mariscada viering (Santiago de Compostela)', 'de' => 'Mariscada-Feier (Santiago de Compostela)'],
                    'description' => [
                        'fr' => 'La mariscada galicienne : plateau de fruits de mer de la ría — percebes (pouce-pieds, rares et chers), nécores (crabes), mejillones (moules en escabeche), almejas (palourdes), camarones (crevettes grises). Avec pan de maíz et albariño DO Rías Baixas. Le repas de la vie, mérité après 2 500 km.',
                        'nl' => 'De Galicische mariscada: zeevruchtenplateau — percebes (zeepokken), nécores (krabben), mejillones (mosselen in escabeche), almejas (mosselen), camarones. Met maïsbrood en albariño DO Rías Baixas.',
                        'de' => 'Die galicische Mariscada: Meeresfrüchteplatte — Percebes (Entenmuscheln), Nécores (Krabben), Mejillones (Muscheln in Escabeche), Almejas (Venusmuscheln), Camarones. Mit Maisbrot und Albariño DO Rías Baixas.',
                    ],
                    'meal_context' => 'restaurant',
                    'restaurant_name' => 'O Curro da Parra ou Restaurante Asador La Bodeguilla',
                    'restaurant_address' => 'Casco histórico, Santiago de Compostela',
                    'price_estimate_eur' => 45.00,
                    'kcal_estimate' => 800,
                    'notes' => [
                        'fr' => '⭐ Le repas de victoire. Peut attendre le lendemain (repos R6-R7). Budget 35-60 € par personne. Les percebes se mangent en tirant le pied (la pulpe est dans la coquille noire). Albariño = accord parfait. Commander aussi la Tarta de Santiago en dessert.',
                        'nl' => '⭐ Het overwinningsgerecht. Kan wachten tot morgen (rustdag R6-R7). Budget 35-60 € per persoon. Albariño = perfecte combinatie. Ook Tarta de Santiago als dessert bestellen.',
                        'de' => '⭐ Das Siegesessen. Kann auf morgen warten (Ruhetag R6-R7). Budget 35-60 € pro Person. Albariño = perfekte Kombination. Auch Tarta de Santiago als Dessert bestellen.',
                    ],
                ],
            ],

            // ── ES-39 Santiago — Tarta de Santiago ──────────────────────────
            [
                'stage_code' => 'ES-39',
                'data' => [
                    'meal_type' => 'snack',
                    'name' => ['fr' => 'Tarta de Santiago (avec Compostela)', 'nl' => 'Tarta de Santiago (met Compostela)', 'de' => 'Tarta de Santiago (mit Compostela)'],
                    'description' => [
                        'fr' => 'Gâteau aux amandes galicien (sans farine — gluten free naturellement), saupoudré de sucre glace formant la Croix de Santiago. IGP. Texture dense et humide, goût amande-citron. LA pâtisserie du pèlerin.',
                        'nl' => 'Galicische amandelcake (zonder bloem — glutenvrij van nature), bestoven met poedersuiker in de vorm van het Kruis van Santiago. IGP. Dichte, vochtige textuur.',
                        'de' => 'Galicischer Mandelkuchen (ohne Mehl — natürlich glutenfrei), mit Puderzucker in Form des Kreuzes von Santiago bestäubt. IGP. Dichte, feuchtige Textur.',
                    ],
                    'meal_context' => 'grocery',
                    'price_estimate_eur' => 4.00,
                    'kcal_estimate' => 350,
                    'notes' => [
                        'fr' => 'Se trouve dans toutes les pâtisseries de la vieille ville. La vraie (avec IGP) se distingue à la croix de l\'ordre de Santiago. Avec albariño ou café con leche. Se conserve 3-5 jours — parfait pour le retour.',
                        'nl' => 'In alle banketbakkerijen van de oude stad. De echte (met IGP) is herkenbaar aan het kruis. Met albariño of café con leche.',
                        'de' => 'In allen Konditoreien der Altstadt. Die echte (mit IGP) erkennt man am Kreuz. Mit Albariño oder Café con leche.',
                    ],
                ],
            ],

            // ── PC-03 Potes — cocido lebaniés ────────────────────────────────
            [
                'stage_code' => 'PC-03',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Cocido lebaniés (Potes)', 'nl' => 'Cocido lebaniés (Potes)', 'de' => 'Cocido lebaniés (Potes)'],
                    'description' => [
                        'fr' => 'Le cocido de montagne de Liébana : haricots de Liébana + chorizo + morcilla + cecina (bœuf fumé séché) + patates + chou. Plus sec que le cocido montañés cantabre, parfumé à la cecina. Spécialité du Camino Lebaniego. Avec orujo de Liébana (eau-de-vie locale) en digestif.',
                        'nl' => 'De bergstoofpot van Liébana: Liébana-bonen + chorizo + morcilla + cecina (gerookt gedroogd rundvlees) + aardappelen + kool. Dryer dan de Cantabrische versie. Met orujo de Liébana (lokale eau-de-vie).',
                        'de' => 'Der Bergeintopf von Liébana: Liébana-Bohnen + Chorizo + Morcilla + Cecina (geräuchertes getrocknetes Rindfleisch) + Kartoffeln + Kohl. Trockener als die kantabrische Version. Mit Orujo de Liébana (lokale Eau-de-Vie).',
                    ],
                    'meal_context' => 'restaurant',
                    'price_estimate_eur' => 15.00,
                    'kcal_estimate' => 1200,
                    'notes' => [
                        'fr' => 'Potes est le haut lieu du cocido lebaniés. Restaurant recommandé : El Tragabuches (spécialités montagnardes). Commencer avec du fromage Picón Bejes-Tresviso en entrée si disponible.',
                        'nl' => 'Potes is het centrum van de cocido lebaniés. Aanbevolen restaurant: El Tragabuches. Als voor-gerecht Picón Bejes-Tresviso kaas nemen als beschikbaar.',
                        'de' => 'Potes ist das Zentrum des Cocido lebaniés. Empfohlenes Restaurant: El Tragabuches. Als Vorspeise Picón Bejes-Tresviso Käse wenn verfügbar.',
                    ],
                ],
            ],

            // ── PC-06 Arenas de Cabrales — cabrales DOP ─────────────────────
            [
                'stage_code' => 'PC-06',
                'data' => [
                    'meal_type' => 'dinner',
                    'name' => ['fr' => 'Cabrales DOP (dégustation en cave, Arenas)', 'nl' => 'Cabrales DOP (degustatie in grot, Arenas)', 'de' => 'Cabrales DOP (Verkostung in Höhle, Arenas)'],
                    'description' => [
                        'fr' => 'Le bleu le plus puissant du monde ? Le Cabrales DOP — fromage de vache/brebis/chèvre affiné 2-4 mois dans les grottes naturelles calcaires des Picos de Europa. Odeur ammoniaquée, saveur explosive (gras + piquant + umami). La cave du Museo del Queso Cabrales propose des dégustations directement dans les grottes.',
                        'nl' => 'De sterkste blauwschimmelkaas ter wereld? Cabrales DOP — koe/schaap/geit kaas 2-4 maanden gerijpt in de Picos-kalksteengrotten. Ammoniaakgeur, explosieve smaak. Degustatie direct in de grotten bij het Museo del Queso Cabrales.',
                        'de' => 'Der stärkste Blauschimmelkäse der Welt? Cabrales DOP — Kuh/Schaf/Ziege-Käse 2-4 Monate in Kalksteinhöhlen der Picos gereift. Ammoniakgeruch, explosive Aromen. Verkostung direkt in den Höhlen im Museo del Queso Cabrales.',
                    ],
                    'meal_context' => 'local_specialty',
                    'restaurant_name' => 'Museo del Queso Cabrales',
                    'restaurant_address' => 'Arenas de Cabrales, Asturias',
                    'price_estimate_eur' => 8.00,
                    'kcal_estimate' => 300,
                    'notes' => [
                        'fr' => '⭐ Expérience unique. La dégustation en cave coûte ~6-8 € (entrée musée incluse). Sur place : vente de Cabrales à emporter (résiste 2-3 jours en sac à dos si bien emballé). Accompagner de pain de maïz local et de sidra.',
                        'nl' => '⭐ Unieke ervaring. Grotdegustatie ~6-8 € (museumingang inbegrepen). Ter plaatse: Cabrales te koop. Met lokaal maïsbrood en sidra.',
                        'de' => '⭐ Einzigartige Erfahrung. Höhlenverkostung ~6-8 € (Museumseintritt inklusive). Vor Ort: Cabrales zu kaufen. Mit lokalem Maisbrot und Sidra.',
                    ],
                ],
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($meals as $item) {
            $stageId = $stagesByCode[$item['stage_code']] ?? null;

            if ($stageId === null) {
                $this->command->warn("MealSeederEspagne : stage {$item['stage_code']} introuvable — skip.");

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

        Log::info('MealSeederEspagne terminé', ['created' => $created, 'updated' => $updated]);
        $this->command->info("MealSeederEspagne : {$created} créés, {$updated} mis à jour.");
    }
}
