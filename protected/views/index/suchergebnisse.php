<?php
/**
 * @var IndexController $this
 * @var string $suchbegriff
 * @var \Solarium\QueryType\Select\Result\Result $ergebnisse
 * @var RISSucheKrits $krits
 * @var string $msg_ok
 * @var string $msg_err
 * @var bool $email_bestaetigt
 * @var bool $email_angegeben
 * @var bool $eingeloggt
 * @var BenutzerIn $ich
 */

$this->pageTitle = Yii::app()->name;

?>
<h1>Suche nach: &quot;<?= CHtml::encode($suchbegriff) ?>&quot;</h1>

<?
$facet_groups = array();

$antrag_typ = array();
$facet = $ergebnisse->getFacetSet()->getFacet('antrag_typ');
foreach ($facet as $value => $count) if ($count > 0) {
	$str = "<li><a href='" . CHtml::encode($krits->cloneKrits()->addAntragTypKrit($value)->getUrl()) . "'>";
	$str .= $value . ' (' . $count . ')';
	$str .= "</a></li>";
	$antrag_typ[] = $str;
}
if (count($antrag_typ) > 0) $facet_groups["Dokumenttypen"] = $antrag_typ;

$wahlperiode = array();
$facet = $ergebnisse->getFacetSet()->getFacet('antrag_wahlperiode');
foreach ($facet as $value => $count) if ($count > 0) {
	$str = "<li><a href='" . CHtml::encode($krits->cloneKrits()->addWahlperiodeKrit($value)->getUrl()) . "'>";
	$str .= $value . ' (' . $count . ')';
	$str .= "</a></li>";
	$wahlperiode[] = $str;
}
if (count($wahlperiode) > 0) $facet_groups["Wahlperiode"] = $wahlperiode;

?>
<div style="float: left; margin: 20px; border: 1px solid #E1E1E8; border-radius: 4px; padding: 5px; background-color: #eee;">
	<?
	if (count($facet_groups) > 0) {
		?>
		<h2>Ergebnisse einschränken</h2>
		<ul>
			<?
			foreach ($facet_groups as $name => $facets) {
				echo "<li><h3>" . CHtml::encode($name) . "</h3><ul>";
				echo implode("", $facets);
				echo "</ul></li>";
			}
			?></ul>
		<br>
	<?
	}
	?>
</div>


<? $this->renderPartial("suchergebnisse_benachrichtigungen", array(
	"eingeloggt"       => $eingeloggt,
	"email_angegeben"  => $email_angegeben,
	"email_bestaetigt" => $email_bestaetigt,
	"ich"              => $ich,
	"msg_err"          => $msg_err,
	"msg_ok"           => $msg_ok,
)); ?>


<br style="clear: both;">
<h2>Suchergebnisse</h2>
<ul>
	<?
	$dokumente = $ergebnisse->getDocuments();
	//$mlt = $ergebnisse->getMoreLikeThis();
	//$ergebnisse->getMoreLikeThis();
	$highlighting = $ergebnisse->getHighlighting();
	foreach ($dokumente as $dokument) {
		$model = AntragDokument::getDocumentBySolrId($dokument->id);
		echo "<li>" . CHtml::link($model->name, $this->createUrl("index/dokument", array("id" => str_replace("Document:", "", $dokument->id)))) . "<blockquote>";
		$highlightedDoc = $highlighting->getResult($dokument->id);
		if ($highlightedDoc) {
			foreach ($highlightedDoc as $field => $highlight) {
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