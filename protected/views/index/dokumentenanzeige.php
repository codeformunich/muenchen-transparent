<?
/**
 * @var int $id
 * @var IndexController $this
 * @var Dokument|null $dokument
 */
?>


<section class="well pdfjs">
	<ul class="breadcrumb">
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
		<?

		/**
		 * @param IRISItemHasDocuments $uebergruppe
		 * @param string $name
		 * @param Dokument $dokument
		 * @param string $link
		 */
		function dokumentenliste($uebergruppe, $name, $dokument, $link)
		{
			if ($link) {
				echo "<li>" . CHtml::link($name, $uebergruppe->getLink()) . "<br></li>";
			} else {
				echo "<li class=\"active\">" . CHtml::encode($name) . "<br></li>";
			}

			if (count($uebergruppe->getDokumente()) > 1) {
				?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= CHtml::encode($dokument->getName()) ?><span class="caret"></span></a>
					<ul class="dropdown-menu">
						<? foreach ($uebergruppe->getDokumente() as $dok) echo "<li>" . CHtml::link($dok->getName(), $dok->getLinkZumDokument()) . "</li>\n" ?>
					</ul>
				</li> <?
			} else {
				echo "<li>" . CHtml::link($dokument->getName(), $dokument->getLinkZumDokument()) . "</li>\n";
			}
		}

		if ($dokument != null) {
			if ($dokument->antrag_id) dokumentenliste(Antrag::model()->findByPk($dokument->antrag_id), "Anträge", $dokument, true);
			else if ($dokument->tagesordnungspunkt_id) dokumentenliste(Tagesordnungspunkt::model()->findByPk($dokument->tagesordnungspunkt_id), "Ergebnisse", $dokument, true);
			else if ($dokument->termin_id) dokumentenliste(Termin::model()->findByPk($dokument->termin_id), "Termin", $dokument, true);
			else if ($dokument->vorgang_id) dokumentenliste(Vorgang::model()->findByPk($dokument->vorgang_id), "Vorgang", $dokument, false);
			else     echo "<li class=\"active\">" . CHtml::encode($dokument->getName()) . "</li>";
		}
		?>
	</ul>
	<div style="position: absolute; top: 10px; right: 14px;"><a href="<?= CHtml::encode($dokument->getLink()) ?>" download><span class="fontello-download"></span> Dokument herunterladen</a></div>

	<?
	$this->renderPartial("pdf_embed", array(
		"url" => '/dokumente/' . $id . '.pdf',
	));
	?>

	<div id="pdf_rechtsvermerk">
		Originaldokument von <a href="http://www.ris-muenchen.de/">www.ris-muenchen.de</a>. München Transparent ist nicht für den Inhalt dieses Dokuments verantwortlich.
	</div>

	<script>
		// Fix the problem that pdf js doesn't get the height automatically (maybe because of the footer)
		function pdf_resize() {
			var $container = $("#mainContainer");
			var container_height = $(window).height() - $("body > footer").height() - $("#main_navbar").height() - 95;
			$container.height(container_height);
			$container.parents(".well").height(container_height + 22);
		}

		$(pdf_resize);
		$(window).resize(pdf_resize);
	</script>
</section>
