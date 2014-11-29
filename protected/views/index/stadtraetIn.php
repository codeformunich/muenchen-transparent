<?php
/**
 * @var StadtraetIn $stadtraetIn
 * @var IndexController $this
 */

$this->pageTitle = $stadtraetIn->getName();


?>
<section class="well">
	<ul class="breadcrumb" style="margin-bottom: 5px;">
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/startseite")) ?>">Startseite</a><br></li>
		<li><a href="<?= CHtml::encode(Yii::app()->createUrl("index/personen")) ?>">Personen</a><br></li>
		<li class="active"><?= CHtml::encode($stadtraetIn->getName()) ?></li>
	</ul>

	<div style="float: right;"><?
		echo CHtml::link("<span class='fontello-right-open'></span> Original-Seite im RIS", $stadtraetIn->getSourceLink());
		?></div>
	<h1><?= CHtml::encode($stadtraetIn->getName()) ?></h1>
</section>

<div class="row">
	<div class="col-md-8">
		<section class="well">
			<table class="table">
				<tbody>
				<tr>
					<th>Fraktion(en):</th>
					<td>
						<ul>
							<? foreach ($stadtraetIn->stadtraetInnenFraktionen as $frakts) {
								echo "<li>" . CHtml::encode($frakts->fraktion->name);
								if ($frakts->fraktion->ba_nr > 0) {
									echo ", Bezirksausschuss " . $frakts->fraktion->ba_nr . " (" . CHtml::encode($frakts->fraktion->bezirksausschuss->name) . ")";
									// @Wird noch nicht zuverlässig erkannt; siehe https://github.com/codeformunich/Ratsinformant/issues/38
								} elseif ($frakts->datum_von > 0 && $frakts->datum_bis > 0) {
									echo " (von " . RISTools::datumstring($frakts->datum_von);
									echo " bis " . RISTools::datumstring($frakts->datum_bis) . ")";
								} elseif ($frakts->datum_von > 0) {
									echo " (seit " . RISTools::datumstring($frakts->datum_von) . ")";
								}
								echo "</li>";
							} ?>
						</ul>
					</td>
				</tr>
				<?
				if (count($stadtraetIn->mitgliedschaften) > 0) {
					?>
					<tr>
						<th>Mitgliedschaften:</th>
						<td>
							<ul>
								<?
								foreach ($stadtraetIn->mitgliedschaften as $mitgliedschaft) {
									$gremium = $mitgliedschaft->gremium;
									echo "<li>";
									echo CHtml::encode($gremium->getName(true));
									if ($gremium->ba_nr > 0) {
										echo " (Bezirksausschuss " . CHtml::link($gremium->ba->name, $gremium->ba->getLink()) . ")";
									}
									echo "</li>\n";
								}
								?>
							</ul>
						</td>
					</tr>
				<?
				}
				if (count($stadtraetIn->antraege) > 0) {
					?>
					<tr>
						<th>Anträge:</th>
						<td>
							<ul>
								<?
								foreach ($stadtraetIn->antraege as $antrag) {
									echo "<li>";
									echo CHtml::link($antrag->getName(true), $antrag->getLink());
									echo " (" . RISTools::datumstring($antrag->gestellt_am) . ")";
									echo "</li>\n";
								}
								?>
							</ul>
						</td>
					</tr>
				<?
				} else {
					$suche = new RISSucheKrits();
					$suche->addVolltextsucheKrit("\"" . $stadtraetIn->getName() . "\"");
					$solr   = RISSolrHelper::getSolrClient("ris");
					$select = $solr->createSelect();
					$suche->addKritsToSolr($select);
					$select->setRows(50);
					$select->addSort('sort_datum', $select::SORT_DESC);

					/** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
					$hl = $select->getHighlighting();
					$hl->setFields('text, text_ocr, antrag_betreff');
					$hl->setSimplePrefix('<b>');
					$hl->setSimplePostfix('</b>');

					/** @var \Solarium\QueryType\Select\Result\Result $ergebnisse */
					$ergebnisse = $solr->select($select);
					$dokumente  = $ergebnisse->getDocuments();

					if (count($dokumente) > 0) {
						echo '<tr><th>Namentlich erwähnt in:</th><td><ul>';
						$highlighting = $ergebnisse->getHighlighting();
						foreach ($dokumente as $dokument) {
							$dok = AntragDokument::getDocumentBySolrId($dokument->id, true);
							if (!$dok) {
								if ($this->binContentAdmin()) {
									echo "<li>Dokument nicht gefunden: " . $dokument->id . "</li>";
								}
							} elseif (!$dok->getRISItem()) {
								if ($this->binContentAdmin()) {
									echo "<li>Dokument-Zuordnung nicht gefunden: " . $dokument->typ . " / " . $dokument->id . "</li>";
								}
							} else {
								$risitem = $dok->getRISItem();
								if (!$risitem) continue;

								$dokurl = $dok->getLinkZumDokument();
								echo '<li style="margin-bottom: 10px;">';
								echo CHtml::link($risitem->getName(true), $risitem->getLink()) . '<br>';
								echo '<a href="' . CHtml::encode($dokurl) . '" class="dokument"><span class="fontello-download"></span> ' . CHtml::encode($dok->name) . '</a>';
								echo '</li>';
							}
						}
						echo '</ul></td></tr>';
					}
				} ?>
				</tbody>
			</table>
		</section>
	</div>
	<section class="col-md-4">
		<div class="well">
			<h2>Weitere Infos</h2>
			<?
			if ($stadtraetIn->web || $stadtraetIn->twitter || $stadtraetIn->facebook || $stadtraetIn->abgeordnetenwatch) {
				echo '<dl>';
				if ($stadtraetIn->web != "") {
					echo '<dt>Homepage:</dt>';
					echo '<dd><a href="' . CHtml::encode($stadtraetIn->web) . '">' . CHtml::encode($stadtraetIn->web) . '</a></dd>' . "\n";
				}
				if ($stadtraetIn->twitter != "") {
					echo '<dt>Twitter:</dt>';
					echo '<dd><a href="https://twitter.com/' . CHtml::encode($stadtraetIn->twitter) . '">@' . CHtml::encode($stadtraetIn->twitter) . '</a></dd>' . "\n";
				}
				if ($stadtraetIn->facebook != "") {
					echo '<dt>Facebook:</dt>';
					echo '<dd><a href="https://www.facebook.com/' . CHtml::encode($stadtraetIn->facebook) . '">Facebook-Profil</a></dd>' . "\n";
				}
				if ($stadtraetIn->abgeordnetenwatch != "") {
					echo '<dt>Abgeordnetenwatch:</dt>';
					echo '<dd><a href="' . CHtml::encode($stadtraetIn->abgeordnetenwatch) . '">Abgeordnetenwatch-Profil</a></dd>' . "\n";
				}
				echo "</dl>";
			}
			?>
			@TODO
		</div>
	</section>
</div>