<?php
/**
 * @var RISBaseController $this
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 */

$dokumente = $ergebnisse->getDocuments();

if (count($dokumente) == 0) { ?>
	<div class="alert alert-error">
		Keine Dokumente gefunden.
	</div>
<? } else {
?>
	<ul>
		<?
		//$mlt = $ergebnisse->getMoreLikeThis();
		//$ergebnisse->getMoreLikeThis();
		$highlighting = $ergebnisse->getHighlighting();
		foreach ($dokumente as $dokument) {
			$dok = AntragDokument::getDocumentBySolrId($dokument->id, true);
			echo "<li>" . CHtml::encode($dok->getRISItem()->getName()) . ": " . CHtml::link($dok->name, $this->createUrl("index/dokument", array("id" => $dok->id)));
			$highlightedDoc = $highlighting->getResult($dokument->id);
			if ($highlightedDoc && count($highlightedDoc) > 0) {
				echo "<blockquote>";
				foreach ($highlightedDoc as $field => $highlight) {
					echo implode(' (...) ', $highlight) . '<br/>';
				}
				echo "</blockquote>";
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
<? }