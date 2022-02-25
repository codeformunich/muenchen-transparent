<?php
/**
 * @var StadtraetIn[][][] $fraktionen
 * @var string $title
 */
?>
<section class="well"><?php
    $insgesamt = 0;
    foreach ($fraktionen as $fraktion)
        $insgesamt += count($fraktion['persons']);
    ?>

    <h2><?= CHtml::encode($title) ?> <span style="float: right"><?= $insgesamt ?></span></h2>

    <ul class="fraktionen_liste"><?php
        usort($fraktionen, function ($val1, $val2) {
            return count($val2['persons']) <=> count($val1['persons']);
        });
        foreach ($fraktionen as $fraktion) {
            echo "<li><a href='" . CHtml::encode($fraktion['link']) . "' class='name'><span class=\"glyphicon glyphicon-chevron-right\"></span>";
            echo "<span class='count'>" . count($fraktion['persons']) . "</span>";
            echo CHtml::encode($fraktion['name']) . "</a><ul class='mitglieder'>";
            $mitglieder = StadtraetIn::sortByName($fraktion['persons']);
            foreach ($mitglieder as $mitglied) {
                echo "<li>";
                echo "<a href='" . CHtml::encode($mitglied->getLink()) . "' class='ris_link'>" . CHtml::encode($mitglied->getName()) . "</a>";
                if ($mitglied->abgeordnetenwatch != "") echo "<a href='" . CHtml::encode($mitglied->abgeordnetenwatch) . "' title='Abgeordnetenwatch' class='abgeordnetenwatch_link'></a>";
                if ($mitglied->web != "") echo "<a href='" . CHtml::encode($mitglied->web) . "' title='Homepage' class='web_link'></a>";
                if ($mitglied->twitter != "") echo "<a href='https://twitter.com/" . CHtml::encode($mitglied->twitter) . "' title='Twitter'           class='twitter_link'>T         </a>";
                if ($mitglied->facebook != "") echo "<a href='https://www.facebook.com/" . CHtml::encode($mitglied->facebook) . "' title='Facebook'          class='fb_link'>     f         </a>";
                echo "</li>\n";
            }
            echo "</ul></li>\n";
        }
        ?></ul>

    <script>
        $(function () {
            var $frakts = $(".fraktionen_liste > li");
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
