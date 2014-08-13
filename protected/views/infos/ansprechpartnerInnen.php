<?php

/**
 * @var array[] $fraktionen
 */

?>

<h2>AnsprechpartnerInnen</h2>


<div class="row" id="listen_holder">
	<div class="col col-lg-6">
		<section>
			<h3>StadträtInnen</h3>

			<ul class="fraktionen_liste"><?
				usort($fraktionen, function ($val1, $val2) {
					if (count($val1) < count($val2)) return 1;
					if (count($val1) > count($val2)) return -1;
					return 0;
				});
				foreach ($fraktionen as $fraktion) {
					/** @var StadtraetIn[] $fraktion */
					$fr = $fraktion[0]->stadtraetInnenFraktionen[0]->fraktion;
					echo "<li><a href='" . CHtml::encode($fr->getLink()) . "' class='name'>";
					echo "<span class='count'>" . count($fraktion) . "</span>";
					echo CHtml::encode($fr->name) . "</a><ul class='mitglieder'>";
					foreach ($fraktion as $str) {
						echo "<li>";
						if ($str->abgeordnetenwatch != "") echo "<a href='" . CHtml::encode($str->abgeordnetenwatch) . "' class='abgeordnetenwatch_link' title='Abgeordnetenwatch'></a>";
						if ($str->web != "") echo "<a href='" . CHtml::encode($str->web) . "' title='Homepage' class='web_link'></a>";
						if ($str->twitter != "") echo "<a href='https://twitter.com/" . CHtml::encode($str->twitter) . "' title='Twitter' class='twitter_link'>T</a>";
						if ($str->facebook != "") echo "<a href='https://www.facebook.com/" . CHtml::encode($str->facebook) . "' title='Facebook' class='fb_link'>f</a>";
						echo "<a href='" . CHtml::encode($str->getLink()) . "' class='ris_link'>" . CHtml::encode($str->name) . "</a>";
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
						var $li = $(this).parents("li").first(),
							is_open = !$li.hasClass("closed");
						$frakts.addClass("closed");
						if (!is_open) $li.removeClass("closed");
					});
				})
			</script>
		</section>

		<section>
			<h3>Städtische Referate</h3>
			...
		</section>
	</div>
	<div class="col col-lg-6">
		<section>
			<h3>Stadtteilpolitik</h3>
			...
		</section>
	</div>
</div>