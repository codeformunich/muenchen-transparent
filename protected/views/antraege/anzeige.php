<?php
/**
 * @var Antrag $antrag
 * @var AntraegeController $this
 * @var string $themenverwandt
 */

$this->pageTitle = $antrag->getName(true);

$assets_base = $this->getAssetsBase();


$personen = array(
	AntragPerson::$TYP_GESTELLT_VON => array(),
	AntragPerson::$TYP_INITIATORIN  => array(),
);
foreach ($antrag->antraegePersonen as $ap) $personen[$ap->typ][] = $ap->person;

$historie = $antrag->getHistoryDiffs();

function zeile_anzeigen($feld, $name, $callback)
{
	if (count($feld) == 0) {
		return;
	} else if (count($feld) == 1) {
		?>
		<tr>
			<th><? echo $name ?></th>
			<td>
				<? $callback($feld[0]); ?>
			</td>
		</tr> <?
	} else {
		?>
		<tr>
			<th><? echo $name ?></th>
			<td>
				<ul>
					<? foreach ($feld as $element) {
						?>
						<li> <?
							$callback($element);
							?> </li> <?
					} ?>
				</ul>
			</td>
		</tr> <?
	}
}

?>

<section class="row">
	<div class="col-md-8">
		<section class="well">
			<h1><?= CHtml::encode($antrag->getName(true)) ?></h1>

			<table class="table antragsdaten">
				<tbody>
				<tr>
					<th>Typ:</th>
					<td>
						<div style="float: right;"><?
							echo CHtml::link("<span class='icon-right-open'></span> Original-Seite im RIS", $antrag->getSourceLink());
							?></div>
						<?
						echo "<strong>" . Yii::t('t', Antrag::$TYPEN_ALLE[$antrag->typ], 1) . "</strong>";
						if ($antrag->antrag_typ != "") echo " (" . CHtml::encode($antrag->antrag_typ) . ")";
						?>
					</td>
				</tr>
				<?
				zeile_anzeigen($personen[AntragPerson::$TYP_INITIATORIN], "Initiiert von:", function ($person) use ($antrag) {
					/** @var Person $person */
					if ($person->stadtraetIn) {
						echo CHtml::link($person->stadtraetIn->name, $person->stadtraetIn->getLink());
						echo " (" . CHtml::encode($person->ratePartei($antrag->gestellt_am)) . ")";
					} else {
						echo CHtml::encode($person->name);
					}
				});
				zeile_anzeigen($personen[AntragPerson::$TYP_GESTELLT_VON], "Gestellt von:", function ($person) use ($antrag) {
					/** @var Person $person */
					if ($person->stadtraetIn) {
						echo CHtml::link($person->stadtraetIn->name, $person->stadtraetIn->getLink());
						echo " (" . CHtml::encode($person->ratePartei($antrag->gestellt_am)) . ")";
					} else {
						echo CHtml::encode($person->name);
					}
				});
				?>
				<tr>
					<th>Gremium:</th>
					<td><?
						if ($antrag->ba_nr > 0) {
							echo CHtml::link("Bezirksausschuss " . $antrag->ba_nr, $this->createUrl("index/ba", array("ba_nr" => $antrag->ba_nr))) . " (" . CHtml::encode($antrag->ba->name) . ")<br>";
						} else {
							echo "Stadtrat<br>";
						}
						echo CHtml::encode(strip_tags($antrag->referat));
						?></td>
				</tr>
				<tr>
					<th>Daten:</th>
					<td>
						<table class="daten"><?
							echo "<tr><th>Antragsnummer:</th><td>" . CHtml::encode($antrag->antrags_nr) . "</td></tr>";
							if ($antrag->gestellt_am > 0 && $antrag->gestellt_am == $antrag->registriert_am) {
								echo "<tr><th>Gestellt u. registriert: </th><td>" . CHtml::encode(RISTools::datumstring($antrag->gestellt_am)) . "</td></tr>\n";
							} else {
								if ($antrag->gestellt_am > 0) echo "<tr><th>Gestellt am:</th><td>" . CHtml::encode(RISTools::datumstring($antrag->gestellt_am)) . "</td></tr>\n";
								if ($antrag->registriert_am > 0) echo "<tr><th>Registriert am:</th><td>" . CHtml::encode(RISTools::datumstring($antrag->registriert_am)) . "</td></tr>\n";
							}
							if ($antrag->bearbeitungsfrist > 0) echo "<tr><th>Bearbeitungsfrist:</th><td>" . CHtml::encode(RISTools::datumstring($antrag->bearbeitungsfrist)) . "</td></tr>\n";
							if ($antrag->fristverlaengerung > 0) echo "<tr><th>Fristverlängerung:</th><td>" . CHtml::encode(RISTools::datumstring($antrag->fristverlaengerung)) . "</td></tr>\n";

							echo "<tr><th>Status:</th><td>";
							echo CHtml::encode($antrag->status);
							if ($antrag->bearbeitung != "") echo " / ";
							echo CHtml::encode($antrag->bearbeitung);
							echo "</td></tr>\n";
							echo "<tr><th>Wahlperiode:</th><td>" . CHtml::encode($antrag->wahlperiode) . "</td></tr>";
							?></table>
					</td>
				</tr>
				<?
				$docs = $antrag->dokumente;
				usort($docs, function ($dok1, $dok2) {
					/**
					 * @var AntragDokument $dok1
					 * @var AntragDokument $dok2
					 */
					$ts1 = RISTools::date_iso2timestamp($dok1->datum);
					$ts2 = RISTools::date_iso2timestamp($dok2->datum);
					if ($ts1 > $ts2) return 1;
					if ($ts1 < $ts2) return -1;
					return 0;
				});
				zeile_anzeigen($docs, "Dokumente:", function ($dok) {
					echo date("d.m.Y", RISTools::date_iso2timestamp($dok->datum)) . ": " . CHtml::link($dok->name, $dok->getOriginalLink());
				});

				if (count($antrag->ergebnisse) > 0) {
					?>
					<tr>
						<th>Behandelt:</th>
						<td>
							<table>
								<thead>
								<tr>
									<th>Termin</th>
									<th>Gremium</th>
									<th>Beschluss</th>
									<th>Entscheidung</th>
								</tr>
								</thead>
								<?
								foreach ($antrag->ergebnisse as $ergebnis) {
									$termin = $ergebnis->sitzungstermin;
									echo '<tr><td>';
									if ($termin) echo CHtml::link($termin->termin, $termin->getLink());
									if ($ergebnis->status != "") echo " (" . CHtml::encode($ergebnis->status) . ")";
									echo '</td><td>';
									if ($termin) echo CHtml::link($termin->gremium->getName(), $termin->getLink());
									echo '</td><td>';
									echo CHtml::encode($ergebnis->beschluss_text);
									echo '</td><td>';
									echo CHtml::encode($ergebnis->entscheidung);
									echo '</td></tr>';
								}
								?>
							</table>
						</td>
					</tr>
				<?
				}

				zeile_anzeigen($antrag->antrag2vorlagen, "Verbundene Stadtratsvorlagen:", function ($element) {
					echo CHtml::link($element->getName(true), $this->createUrl("antraege/anzeigen", array("id" => $element->id)));
				});

				zeile_anzeigen($antrag->vorlage2antraege, "Verbundene Stadtratsanträge:", function ($element) {
					echo CHtml::link($element->getName(true), $this->createUrl("antraege/anzeigen", array("id" => $element->id)));
				});

				if (count($historie) > 0) {
					?>
					<tr>
						<th>Historie: <span class="icon - info - circled" title="Seit dem 1. April 2014" style="font - size: 12px; color: gray;"></span></th>
						<td>
							<ol>
								<? foreach ($historie as $hist) {
									echo " <li>" . $hist->getDatum() . ": <ul> ";
									$diff = $hist->getFormattedDiff();
									foreach ($diff as $d) {
										echo "<li><strong> " . $d->getFeld() . ":</strong> ";
										echo "<del> " . $d->getAlt() . "</del> => <ins> " . $d->getNeu() . "</ins></li> ";
									}
									echo "</ul></li>\n";
								} ?>
							</ol>
						</td>
					</tr>
				<?
				}
				/** @var IRISItem[] $vorgang_items */
				if ($antrag->vorgang) {
					zeile_anzeigen($antrag->vorgang->getRISItemsByDate(), "Verwandte Seiten:", function ($item) {
						echo CHtml::link($item->getName(true), $item->getLink());
					});
				}
				?>

				</tbody>
			</table>
		</section>
	</div>
	<section class="col-md-4">

		<form method="POST" action="<?= Yii::app()->createUrl("antraege/anzeigen", array("id" => $antrag->id)) ?>" class="abo_button row_head"
			  style="min-height: 80px; text-align: center;">
			<?
			if ($antrag->vorgang && $antrag->vorgang->istAbonniert($this->aktuelleBenutzerIn())) {
				?>
				<button type="submit" name="<?= AntiXSS::createToken("deabonnieren") ?>" class="btn btn-success btn-raised btn-lg"><span class="email">@</span> Abonniert</button>
			<? } else { ?>
				<button type="submit" name="<?= AntiXSS::createToken("abonnieren") ?>" class="btn btn-info btn-raised btn-lg"><span class="email">@</span> Nicht abonniert</button>
			<? } ?>
		</form>

		<div class="well">
			<h2 style="font-size: 16px; font-weight: bold; text-align: center; padding: 2px; margin: 0;">Könnte themenverwandt sein</h2>
			<ul class="antragsliste" id="themenverwandt"><?= $themenverwandt ?></ul>
		</div>
	</section>
</section>
