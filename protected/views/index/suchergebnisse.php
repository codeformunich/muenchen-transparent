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
 * @var bool $wird_benachrichtigt
 * @var BenutzerIn $ich
 */

$this->pageTitle = Yii::app()->name;

?>
	<h1>Suche nach: &quot;<?= CHtml::encode($suchbegriff) ?>&quot;</h1>

<?


if ($msg_ok != "") {
	?>
	<div class="alert alert-success">
		<?php echo $msg_ok; ?>
	</div>
<?
}
if ($msg_err != "") {
	?>
	<div class="alert alert-error">
		<?php echo $msg_err; ?>
	</div>
<?
}


$facet_groups = array();

$antrag_typ = array();
$facet = $ergebnisse->getFacetSet()->getFacet('antrag_typ');
foreach ($facet as $value => $count) if ($count > 0) {
	$str = "<li><a href='" . RISTools::bracketEscape(CHtml::encode($krits->cloneKrits()->addAntragTypKrit($value)->getUrl())) . "'>";
	if (isset(Antrag::$TYPEN_ALLE[$value])) {
		$x = explode("|", Antrag::$TYPEN_ALLE[$value]);
		$str .= $x[1] . ' (' . $count . ')';
	} elseif ($value == "stadtrat_termin") $str .= 'Stadtrats-Termin (' . $count . ')'; elseif ($value == "ba_termin") $str .= 'BA-Termin (' . $count . ')'; else $str .= $value . " (" . $count . ")";
	$str .= "</a></li>";
	$antrag_typ[] = $str;
}
if (count($antrag_typ) > 0) $facet_groups["Dokumenttypen"] = $antrag_typ;

$wahlperiode = array();
$facet = $ergebnisse->getFacetSet()->getFacet('antrag_wahlperiode');
foreach ($facet as $value => $count) if ($count > 0) {
	$str = "<li><a href='" . RISTools::bracketEscape(CHtml::encode($krits->cloneKrits()->addWahlperiodeKrit($value)->getUrl())) . "'>";
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
				foreach ($facet_groups as $name => $facets) if (count($facets) > 1) {
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
	"eingeloggt"          => $eingeloggt,
	"email_angegeben"     => $email_angegeben,
	"email_bestaetigt"    => $email_bestaetigt,
	"wird_benachrichtigt" => $wird_benachrichtigt,
	"ich"                 => $ich,
	"krits"               => $krits
));

?>
<br style="clear: both;">
<h2>Suchergebnisse</h2>
<?

if ($krits->getKritsCount() > 0) $this->renderPartial("../benachrichtigungen/suchergebnisse_liste", array(
	"ergebnisse"  => $ergebnisse,
));