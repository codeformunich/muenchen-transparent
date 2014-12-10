<?php

/**
 * @var IndexController $this
 * @var Antrag[] $antraege
 * @var string|null $neuere_url_ajax
 * @var string|null $neuere_url_std
 * @var string|null $aeltere_url_ajax
 * @var string|null $aeltere_url_std
 * @var bool $weiter_links_oben
 * @var string $datum
 * @var string $title
 * @var float $geo_lng
 * @var float $geo_lat
 * @var float $radius
 * @var OrtGeo $naechster_ort
 * @var Rathausumschau[] $rathausumschauen
 */

if (isset($title) && $title !== null) {
	$erkl_str = CHtml::encode($title);
} elseif (isset($datum)) {
	if ($datum == date("Y-m-d", time() - 3600 * 24) . " 00:00:00") $erkl_str = "des letzten Tages";
	else {
		$erkl_str = RISTools::datumstring($datum);
		if ($erkl_str > 0) $erkl_str = "vom " . $erkl_str;
		else $erkl_str = "von " . $erkl_str;
	}
	$erkl_str = "Stadtratsdokumente " . $erkl_str;
} elseif (isset($datum_von) && isset($datum_bis)) {
	$erkl_str = "Dokumente vom " . RISTools::datumstring($datum_von) . " bis " . RISTools::datumstring($datum_bis);
} else {
	$erkl_str = "Stadtratsdokumente: etwa ${radius}m um \"" . CHtml::encode($naechster_ort->ort) . "\"";
}

if (!isset($rathausumschauen)) $rathausumschauen = array();

$datum_nav = ((isset($neuere_url_ajax) && $neuere_url_ajax !== null) || (isset($aeltere_url_ajax) && $aeltere_url_ajax !== null));
if (count($antraege) > 0) {
	echo '<h3>' . $erkl_str . '</h3><br>';

	if ($weiter_links_oben && $datum_nav) {
		?>
		<div class="antragsliste_nav">
			<?
			if (isset($neuere_url_ajax) && $neuere_url_ajax !== null) {
				?>
				<div class="neuere_caller">
					<a href="<?= CHtml::encode($neuere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($neuere_url_ajax) ?>');" rel="next"><span
							class="glyphicon glyphicon-chevron-left"></span> Neuere Dokmente</a>
				</div>
			<?
			}
			if (isset($aeltere_url_ajax) && $aeltere_url_ajax !== null) {
				?>
				<div class="aeltere_caller">
					<a href="<?= CHtml::encode($aeltere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($aeltere_url_ajax) ?>');" rel="next">Ältere
						Dokmente <span class="glyphicon glyphicon-chevron-right"></span></a>
				</div>
			<?
			}
			?>
		</div>
	<?
	}
	echo '<ul class="antragsliste2">';
	$akt_datum = null;

	$by_date = array();
	foreach ($rathausumschauen as $ru) {
		if (!isset($by_date[$ru->datum])) $by_date[$ru->datum] = array();
		$by_date[$ru->datum][] = $ru;
	}
	foreach ($antraege as $ant) {
		if (!method_exists($ant, "getName")) {
			echo '<li class="panel panel-danger">
			<div class="panel-heading">Fehler</div><div class="panel-body">' . get_class($ant) . "</div></li>";
		} else {
			$datum = date("Y-m-d", $ant->getDokumentenMaxTS());
			if (!isset($by_date[$datum])) $by_date[$datum] = array();
			$by_date[$datum][] = $ant;
		}
	}
	krsort($by_date);

	foreach ($by_date as $date => $entries) foreach ($entries as $entry) {
		if (is_a($entry, "Rathausumschau")) {
			/** @var Rathausumschau $entry */
			echo '<li class="panel panel-success">
			<div class="panel-heading"><a href="' . CHtml::encode($entry->getLink()) . '"><span>';
			echo CHtml::encode($entry->getName(true)) . '</a></span></div>';
			echo '<div class="panel-body">';

			echo "<div class='add_meta'>";
			echo date("d.m.", RISTools::date_iso2timestamp($entry->datum));
			echo "</div>";

			$inhalt = $entry->inhaltsverzeichnis();
			if (count($inhalt) > 0) echo '<ul class="toc">';
			foreach ($inhalt as $inh)  {
				if ($inh["link"]) echo '<li>' . CHtml::link($inh["titel"], $inh["link"]) . '</li>';
				else echo '<li>' . CHtml::encode($inh["titel"]) . '</li>';
			}
			if (count($inhalt) > 0) echo '</ul>';

			echo '</div>';
			echo '</li>';
		} else {
			/** @var Antrag $entry */
			$doklist  = "";
			foreach ($entry->dokumente as $dokument) {
				$dokurl = $dokument->getLinkZumDokument();
				$doklist .= "<li><a href='" . CHtml::encode($dokurl) . "'";
				if (substr($dokurl, strlen($dokurl) - 3) == "pdf") $doklist .= ' class="pdf"';
				$doklist .= ">" . CHtml::encode($dokument->getName(false)) . "</a></li>";
				$dat = RISTools::date_iso2timestamp($dokument->getDate());
			}

			$titel = $entry->getName(true);
			echo '<li class="panel panel-primary">
			<div class="panel-heading"><a href="' . CHtml::encode($entry->getLink()) . '"';
			if (mb_strlen($titel) > 110) echo ' title="' . CHtml::encode($titel) . '"';
			echo '><span>';
			echo CHtml::encode($titel) . '</a></span></div>';
			echo '<div class="panel-body">';


			echo "<div class='add_meta'>";
			$parteien = array();
			foreach ($entry->antraegePersonen as $person) {
				$name   = $person->person->getName(true);
				$partei = $person->person->ratePartei($entry->gestellt_am);
				$key    = ($partei ? $partei : $name);
				if (!isset($parteien[$key])) $parteien[$key] = array();
				$parteien[$key][] = $name;
			}

			$p_strs = array();
			foreach ($parteien as $partei => $personen) {
				$personen_net = array();
				foreach ($personen as $p) if ($p != $partei) $personen_net[] = $p;
				$str = "<span class='partei' title='" . CHtml::encode(implode(", ", $personen_net)) . "'>";
				$str .= CHtml::encode($partei);
				$str .= "</span>";
				$p_strs[] = $str;
			}
			if (count($p_strs) > 0) echo implode(", ", $p_strs) . ", ";

			if ($entry->ba_nr > 0) echo "<span title='" . CHtml::encode("Bezirksausschuss " . $entry->ba_nr . " (" . $entry->ba->name . ")") . "' class='ba'>BA " . $entry->ba_nr . "</span>, ";

			echo date("d.m.", $entry->getDokumentenMaxTS());
			echo "</div>";

			echo "<ul class='dokumente'>";
			echo $doklist;
			echo "</ul></div></li>\n";
		}
	}
	echo '</ul>';
}

if ($datum_nav) {
	?>
	<div class="antragsliste_nav">
		<?
		if (isset($neuere_url_ajax) && $neuere_url_ajax !== null) {
			?>
			<div class="neuere_caller">
				<a href="<?= CHtml::encode($neuere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($neuere_url_ajax) ?>');" rel="next"><span
						class="glyphicon glyphicon-chevron-left"></span> Neuere Dokmente</a>
			</div>
		<?
		}
		if (isset($aeltere_url_ajax) && $aeltere_url_ajax !== null) {
			?>
			<div class="aeltere_caller">
				<a href="<?= CHtml::encode($aeltere_url_std) ?>" onClick="return index_datum_dokumente_load(this, '<?= CHtml::encode($aeltere_url_ajax) ?>');" rel="next">Ältere
					Dokmente
					<span class="glyphicon glyphicon-chevron-right"></span></a>
			</div>
		<?
		}
		?>
	</div>
<?
}
