<?php
/**
 * @var IndexController $this
 * @var AntragDokument $dokument
 * @var array|AntragDokument[] $morelikethis
 */


?>
<h1><?=CHtml::encode($dokument->name)?></h1>

<?=CHtml::link("Original", $dokument->getOriginalLink())?><br><br>

<div style="float: right; max-width: 300px;" class="well">
	<h3>Ähnliche Dokumente</h3>
	<ul>
		<? foreach ($morelikethis as $doc) {
			echo "<li style='margin-bottom: 10px;'>";
			$name = $doc->name;
			if ($doc->antrag) $name = $doc->antrag->getName() . " - " . $name;
			if ($doc->termin) $name = $doc->termin->termin . " - " . $name;
			echo CHtml::link($name, $this->createUrl("index/dokument", array("id" => $doc->id)));
			echo "</li>";
		} ?>
	</ul>
</div>

OCR:
<blockquote>
	<?=nl2br(CHtml::encode($dokument->text_ocr_corrected))?>
</blockquote>

PDF-Text:
<blockquote>
	<?=nl2br(CHtml::encode($dokument->text_pdf))?>
</blockquote>

