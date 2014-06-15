<?php
/**
 * @var BenutzerIn $benutzerIn
 * @var array $data
 */
$css = file_get_contents(Yii::app()->getBasePath() . "/assets/styles_mail.css") . "\n\n";
?>
<!DOCTYPE html>
<html lang="de">
<head>
	<meta charset="utf-8">
	<title>Neue Dokumente im Münchner RIS</title>
	<style><?php echo $css; ?></style>
</head>

<body>
<h2>Hallo,</h2>
seit der letzten E-Mail-Benachrichtigung wurden folgende neuen Dokumente gefunden, die deinen Benachrichtigungseinstellungen entsprechen:<br><br>
<?php

if (count($data["antraege"]) > 0) {
	?>
	<section class="fullsize">
		<h3>Anträge & Vorlagen</h3>
		<ul class="antragsliste">
			<?
			foreach ($data["antraege"] as $dat) {
				/** @var Antrag $antrag */
				$antrag = $dat["antrag"];

				echo "<li class='listitem'><div class='antraglink'><a href='" . CHtml::encode($antrag->getLink()) . "' title='" . CHtml::encode($antrag->getName()) . "'>";
				echo CHtml::encode($antrag->getName()) . "</a></div>";

				$dokumente_strs = array();
				$queries        = array();
				$max_date       = 0;
				$doklist        = "";
				foreach ($dat["dokumente"] as $dok) {
					/** @var AntragDokument $dokument */
					$dokument = $dok["dokument"];
					$dokurl   = $dokument->getOriginalLink();
					$doklist .= "<li><a href='" . CHtml::encode($dokurl) . "'";
					if (substr($dokurl, strlen($dokurl) - 3) == "pdf") $doklist .= ' class="pdf"';
					$doklist .= ">" . CHtml::encode($dokument->name) . "</a></li>";
					$dat = RISTools::date_iso2timestamp($dokument->datum);
					if ($dat > $max_date) $max_date = $dat;

					foreach ($dok["queries"] as $qu) {
						/** @var RISSucheKrits $qu */
						$name = $qu->getTitle();
						if (!in_array($name, $queries)) $queries[] = $name;
					}
				}

				echo "<div class='add_meta'>";
				$parteien = array();
				foreach ($antrag->antraegePersonen as $person) {
					$name   = $person->person->name;
					$partei = $person->person->ratePartei($antrag->gestellt_am);
					if (!$partei) {
						$parteien[$name] = array($name);
					} else {
						if (!isset($parteien[$partei])) $parteien[$partei] = array();
						$parteien[$partei][] = $person->person->name;
					}
				}

				$p_strs = array();
				foreach ($parteien as $partei => $personen) {
					$str = "<span class='partei' title='" . CHtml::encode(implode(", ", $personen)) . "'>";
					$str .= CHtml::encode($partei);
					$str .= "</span>";
					$p_strs[] = $str;
				}
				if (count($p_strs) > 0) echo implode(", ", $p_strs) . ", ";

				if ($antrag->ba_nr > 0) echo " <span title='" . CHtml::encode("Bezirksausschuss " . $antrag->ba_nr . " (" . $antrag->ba->name . ")") . "' class='ba'>BA " . $antrag->ba_nr . "</span>, ";

				echo date("d.m.", $max_date);
				echo "</div>";

				echo "<ul class='dokumente'>";
				echo $doklist;
				echo "</ul>";

				echo "<div class='gefunden_ueber'>";
				if (count($queries) == 1) {
					echo "Gefunden über: \"" . $queries[0] . "\"";
				} else {
					echo "Gefunden über: \"" . implode("\"<br>\"", $queries) . "\"";
				}
				echo "</div>";

				echo "</li>\n";
			}

			?></ul>
	</section>
<?
}
unset($antrag);

if (count($data["termine"]) > 0) {
	?>
	<section class="fullsize">
		<h3>Sitzungen</h3>
		<ul class="antragsliste">
			<?
			foreach ($data["termine"] as $dat) {
				/** @var Termin $termin */
				$termin = $dat["termin"];

				echo "<li class='listitem'><div class='antraglink'><a href='" . CHtml::encode($termin->getLink()) . "' title='" . CHtml::encode($termin->getName()) . "'>";
				echo CHtml::encode($termin->getName()) . "</a></div>";

				$dokumente_strs = array();
				$queries        = array();
				$max_date       = 0;
				$doklist        = "";
				foreach ($dat["dokumente"] as $dok) {
					/** @var AntragDokument $dokument */
					$dokument = $dok["dokument"];
					$dokurl   = $dokument->getOriginalLink();
					$doklist .= "<li><a href='" . CHtml::encode($dokurl) . "'";
					if (substr($dokurl, strlen($dokurl) - 3) == "pdf") $doklist .= ' class="pdf"';
					$doklist .= ">" . CHtml::encode($dokument->name) . "</a></li>";
					$dat = RISTools::date_iso2timestamp($dokument->datum);
					if ($dat > $max_date) $max_date = $dat;

					foreach ($dok["queries"] as $qu) {
						/** @var RISSucheKrits $qu */
						$name = $qu->getTitle();
						if (!in_array($name, $queries)) $queries[] = $name;
					}
				}

				echo "<ul class='dokumente'>";
				echo $doklist;
				echo "</ul>";

				echo "<div class='gefunden_ueber'>";
				if (count($queries) == 1) {
					echo "Gefunden über: \"" . $queries[0] . "\"";
				} else {
					echo "Gefunden über: \"" . implode("\"<br>\"", $queries) . "\"";
				}
				echo "</div>";
				echo "</li>\n";
			}

			?></ul>
	</section>
<?
}

$url = Yii::app()->createUrl("benachrichtigungen/index", array("code" => $benutzerIn->getBenachrichtigungAbmeldenCode()));
?>
<br>
<footer>
	Liebe Grüße,<br>
	&nbsp;
	Das Ratsinformanten-Team
	<br><br>
	PS: Falls du diese Benachrichtigung nicht mehr erhalten willst, kannst du sie <a href="<?php echo CHtml::encode($url); ?>">hier abbestellen</a>.
</footer>
</body>
</html>
