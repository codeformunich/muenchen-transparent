<?php
/**
 * @var RISBaseController $this
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 */

$dokumente = $ergebnisse->getDocuments();

if (count($dokumente) == 0) {
	?>
	<div class="alert alert-error">
		Keine Dokumente gefunden.
	</div>
<?
} else {
	?>
	<div class="suchergebnisse_holder">
		<ul class="suchergebnisliste">
			<?
			//$mlt = $ergebnisse->getMoreLikeThis();
			//$ergebnisse->getMoreLikeThis();
			$highlighting = $ergebnisse->getHighlighting();
			foreach ($dokumente as $dokument) {
				$dok = AntragDokument::getDocumentBySolrId($dokument->id, true);
				if (!$dok) {
					echo "<li>Dokument nicht gefunden: " . $dokument->id . "</li>";
				} elseif (!$dok->getRISItem()) {
					echo "<li>Dokument-Zuordnung nicht gefunden: " . $dokument->typ . " / " . $dokument->id . "</li>";
				} else {
					echo "<li>";
					$risitem = $dok->getRISItem();
					//$dokurl = $this->createUrl("index/dokument", array("id" => $dok->id));
					$dokurl = $dok->getOriginalLink();
					echo "<div class='datum'>" . CHtml::encode(date("d.m.Y", RISTools::date_iso2timestamp($dok->datum))) . "</div>";
					echo "<div class='dokument'><a href='" . CHtml::encode($dokurl) . "'><span class='icon-right-open'></span> " . CHtml::encode($dok->name) . " <span class='icon-download'></span></a></div>";
					echo "<div class='antraglink'><a href='" . CHtml::encode($risitem->getLink()) . "' title='" . CHtml::encode($risitem->getName()) . "'>";
					echo CHtml::encode($risitem->getName(true)) . "</a></div>";

					$highlightedDoc = $highlighting->getResult($dokument->id);
					if ($highlightedDoc && count($highlightedDoc) > 0) {
						echo "<blockquote>";
						foreach ($highlightedDoc as $field => $highlight) {
							echo implode(' (...) ', $highlight) . '<br/>';
						}
						echo "</blockquote>";
					}
				}

				/*
				$mltResult = $mlt->getResult($dokument->id);
				if($mltResult){
					echo 'Max score: '.$mltResult->getMaximumScore().'<br/>';
					echo 'NumFound: '.$mltResult->getNumFound().'<br/>';
					echo 'Num. fetched: '.count($mltResult).'<ul>';
					foreach($mltResult AS $mltDoc) {
						echo '<li>MLT result doc: ';
						$dokument = AntragDokument::model()->findByPk(str_replace("Document:", "", $mltDoc->id));
						echo $dokument->url;
						echo '</li>';
					}
					echo "</ul>";
				}else{
					echo 'No MLT results';
				}
				*/
				echo "</li>";
			} ?>
		</ul>
	</div>
<? }