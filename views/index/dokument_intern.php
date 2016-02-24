<?php
/**
 * @var IndexController $this
 * @var Dokument $dokument
 * @var null|Dokument[] $morelikethis
 */


?>
<h1><?= CHtml::encode($dokument->name) ?></h1>

<?= CHtml::link("Original", $dokument->getLinkZumDokument()) ?><br><br>

<div style="float: right; max-width: 300px;" class="well">
	<h3>Ähnliche Dokumente</h3>
	<? if (is_array($morelikethis)) {
		echo "<ul>";
		foreach ($morelikethis as $doc) {
			echo "<li style='margin-bottom: 10px;'>";
			$name = $doc->name;
			if ($doc->antrag) $name = $doc->antrag->getName() . " - " . $name;
			if ($doc->termin) $name = $doc->termin->termin . " - " . $name;
			echo CHtml::link($name, $this->createUrl("index/dokument", array("id" => $doc->id)));
			echo "</li>";
		}
		echo "</ul>";
	} else echo "<i>konnte Liste nicht laden</i>"; ?>
</div>

OCR:
<blockquote>
	<?= nl2br(CHtml::encode($dokument->text_ocr_corrected)) ?>
</blockquote>

PDF-Text:
<blockquote>
	<?= nl2br(CHtml::encode($dokument->text_pdf)) ?>
</blockquote>

