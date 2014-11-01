<?
/**
 * @var Gremium[] $gremien
 * @var string $title
 */
?>
<section class="well"><?
	?>

	<h2><?=CHtml::encode($title)?></h2>

	<ul class="ausschuesse_liste"><?
		usort($gremien, function ($gr1, $gr2) {
			/** @var Gremium $gr1 */
			/** @var Gremium $gr2 */
			return strnatcasecmp($gr1->name, $gr2->name);
		});
		foreach ($gremien as $gremium) {
			if (count($gremium->mitgliedschaften) == 0) continue;
			if (mb_strpos($gremium->name, "Vollgremium") !== false) continue;

			echo "<li><a href='#' class='name'><span class=\"glyphicon glyphicon-chevron-right\"></span>";
			echo "<span class='count'>" . count($gremium->mitgliedschaften) . "</span>";
			echo CHtml::encode($gremium->getName()) . "</a><ul class='mitglieder'>";
			$mitglieder = array();
			foreach ($gremium->mitgliedschaften as $m) $mitglieder[] = $m->stadtraetIn;
			$mitglieder = StadtraetIn::sortByName($mitglieder);
			foreach ($mitglieder as $mitglied) {
				echo "<li>";
				echo "<a href='" . CHtml::encode($mitglied->getLink()) . "' class='ris_link'>"    . CHtml::encode($mitglied->getName()        ) .                                                            "</a>";
				if ($mitglied->abgeordnetenwatch != "") echo "<a href='"                          . CHtml::encode($mitglied->abgeordnetenwatch) . "' title='Abgeordnetenwatch' class='abgeordnetenwatch_link'></a>";
				if ($mitglied->web               != "") echo "<a href='"                          . CHtml::encode($mitglied->web              ) . "' title='Homepage'          class='web_link'>             </a>";
				if ($mitglied->twitter           != "") echo "<a href='https://twitter.com/"      . CHtml::encode($mitglied->twitter          ) . "' title='Twitter'           class='twitter_link'>T         </a>";
				if ($mitglied->facebook          != "") echo "<a href='https://www.facebook.com/" . CHtml::encode($mitglied->facebook         ) . "' title='Facebook'          class='fb_link'>     f         </a>";
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
