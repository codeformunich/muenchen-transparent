<?php
/**
 * @var IndexController $this
 * @var string $suchbegriff
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 */

$this->pageTitle = Yii::app()->name;

?>
<h1>Suche nach: &quot;<?=CHtml::encode($suchbegriff)?>&quot;</h1>


<h2>Facets</h2>
<?
$facet = $ergebnisse->getFacetSet()->getFacet('antrag_typ');
foreach($facet as $value => $count) {
	echo $value . ' [' . $count . ']<br/>';
}
?>
<br>
<h2>MLT</h2>
<?

?>
<h2>Suchergebnisse</h2>
<ul>
	<?
	$dokumente = $ergebnisse->getDocuments();
	//$mlt = $ergebnisse->getMoreLikeThis();
	//$ergebnisse->getMoreLikeThis();
	$highlighting = $ergebnisse->getHighlighting();
	foreach ($dokumente as $dokument) {
		/** @var AntragDokument $model */
		$model = AntragDokument::model()->findByPk(str_replace("Document:", "", $dokument->id));
		echo "<li>" . CHtml::link($model->name, $this->createUrl("index/dokument", array("id" => str_replace("Document:", "", $dokument->id)))) . "<blockquote>";
		$highlightedDoc = $highlighting->getResult($dokument->id);
		if($highlightedDoc){
			foreach($highlightedDoc as $field => $highlight) {
				echo implode(' (...) ', $highlight) . '<br/>';
			}
		}
		echo "</blockquote>";

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