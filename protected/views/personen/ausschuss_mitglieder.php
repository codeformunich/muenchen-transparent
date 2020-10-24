<?php
/**
 * @var Gremium[] $gremien
 * @var string $title
 */

$gremienzahl = 0;
foreach ($gremien as $gremium) {
    if (count($gremium->mitgliedschaften) == 0) continue;
    if (mb_strpos($gremium->name, "Vollgremium") !== false) continue;
    $gremienzahl++;
}
if ($gremienzahl > 0) {
    ?>

    <section class="well"><?php
        ?>

        <h2><?= CHtml::encode($title) ?></h2>

        <ul class="ausschuesse_liste"><?php
            usort($gremien, function ($gr1, $gr2) {
                /** @var Gremium $gr1 */
                /** @var Gremium $gr2 */
                return strnatcasecmp($gr1->getName(true), $gr2->getName(true));
            });
            foreach ($gremien as $gremium) {
                if (count($gremium->mitgliedschaften) == 0) continue;
                if (mb_strpos($gremium->name, "Vollgremium") !== false) continue;

                $aktiveMitgliedschaften = [];
                foreach ($gremium->mitgliedschaften as $mitgliedschaft) {
                    if ($mitgliedschaft->mitgliedschaftAktiv()) {
                        if (!isset($aktiveMitgliedschaften[$mitgliedschaft->stadtraetIn_id])) {
                            $aktiveMitgliedschaften[$mitgliedschaft->stadtraetIn_id] = [
                                'name' => $mitgliedschaft->stadtraetIn->getName(), // Laden erzwingen, ansonsten liefert usort eine Fehlermeldung
                                'funktionen' => [],
                                'mitglied' => $mitgliedschaft->stadtraetIn,
                            ];
                        }
                        if ($mitgliedschaft->funktion != '' && !in_array($mitgliedschaft->funktion, ['UA-Mitglied', 'Mitglied'])) {
                            $aktiveMitgliedschaften[$mitgliedschaft->stadtraetIn_id]['funktionen'][] = $mitgliedschaft->funktion;
                        }
                    }
                }
                if (count($aktiveMitgliedschaften) === 0) continue;
                $aktiveMitgliedschaften = array_values($aktiveMitgliedschaften);

                echo "<li><a href='#' class='name'><span class=\"glyphicon glyphicon-chevron-right\"></span>";
                echo "<span class='count'>" . count($aktiveMitgliedschaften) . "</span>";
                echo CHtml::encode($gremium->getName(true)) . "</a><ul class='mitglieder'>";

                usort($aktiveMitgliedschaften, function ($mitgliedschaft1, $mitgliedschaft2) {
                    return StadtraetIn::sortByNameCmp($mitgliedschaft1['name'], $mitgliedschaft2['name']);
                });

                foreach ($aktiveMitgliedschaften as $mitgliedschaft) {
                    /** @var StadtraetIn $mitglied */
                    $mitglied = $mitgliedschaft['mitglied'];
                    $funktionen = implode(', ', $mitgliedschaft['funktionen']);
                    echo "<li>";
                    echo "<a href='" . CHtml::encode($mitglied->getLink()) . "' class='ris_link'>" . CHtml::encode($mitglied->getName()) . "</a>";
                    if ($funktionen && !preg_match("/^mitglied/siu", $funktionen)) echo ' <span class="zusatzdaten">(' . CHtml::encode($funktionen) . ')</span>';
                    /*
                    if ($mitgliedschaft->datum_bis !== null || $mitgliedschaft->datum_von != "2014-05-01") {
                        // @TODO Datum der Legislaturperiode flexibilisieren
                        echo ' <span class="zusatzdaten">';
                        if ($mitgliedschaft->datum_von != "2014-05-01") echo "seit " . RISTools::datumstring($mitgliedschaft->datum_von);
                        if ($mitgliedschaft->datum_von != "2014-05-01" && $mitgliedschaft->datum_bis !== null) echo ", ";
                        if ($mitgliedschaft->datum_bis !== null) echo "bis " . RISTools::datumstring($mitgliedschaft->datum_bis);
                        echo '</span>';
                    }
                    */
                    if ($mitglied->abgeordnetenwatch != "") echo "<a href='" . CHtml::encode($mitglied->abgeordnetenwatch) . "' title='Abgeordnetenwatch' class='abgeordnetenwatch_link'></a>";
                    if ($mitglied->web != "") echo "<a href='" . CHtml::encode($mitglied->web) . "' title='Homepage'          class='web_link'>             </a>";
                    if ($mitglied->twitter != "") echo "<a href='https://twitter.com/" . CHtml::encode($mitglied->twitter) . "' title='Twitter'           class='twitter_link'>T         </a>";
                    if ($mitglied->facebook != "") echo "<a href='https://www.facebook.com/" . CHtml::encode($mitglied->facebook) . "' title='Facebook'          class='fb_link'>     f         </a>";
                    echo "</li>\n";
                }
                echo "</ul></li>\n";
            }
            ?></ul>

        <script>
            $(function () {
                var $frakts = $(".ausschuesse_liste > li");
                $frakts.addClass("closed").find("> a").click(function (ev) {
                    if (ev.which == 2 || ev.which == 3) return;
                    ev.preventDefault();
                    var $li = $(this).parents("li").first();
                    if ($li.hasClass("closed")) {
                        $li.removeClass("closed");
                        $li.find(".glyphicon").removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
                    } else {
                        $li.addClass("closed");
                        $li.find(".glyphicon").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
                    }
                });
            })
        </script>
    </section>

<?php } // gremienzahl > 0 ?>
