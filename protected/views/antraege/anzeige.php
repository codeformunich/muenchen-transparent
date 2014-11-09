<?php
/**
 * @var Antrag $antrag
 * @var AntraegeController $this
 */

$this->pageTitle = $antrag->getName(true);

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

$name = $antrag->getName(true);
?>

<section class="row">
<div class="col-md-8">
	<section class="well">
		<div style="float: right;"><?
			echo CHtml::link("<span class='fontello-right-open'></span> Original-Seite im RIS", $antrag->getSourceLink());
			?></div>
		<h1 class="small"><? 	echo "<strong>" . Yii::t('t', Antrag::$TYPEN_ALLE[$antrag->typ], 1) . "</strong>";
				if ($antrag->antrag_typ != "") echo " (" . CHtml::encode($antrag->antrag_typ) . ")"; ?></h1>

		<p style="font-size: 18px;"><?= CHtml::encode($name) ?></p>

		<table class="table antragsdaten">
			<tbody>
			<tr>
				<th>Schlagworte:</th>
				<td><?
					if (count($antrag->tags) == 0) echo '<em>noch keine</em>';
					else {
						echo '<ul class="antrags_tags">';
						foreach ($antrag->tags as $tag) {
							echo '<li>' . $tag->getNameLink();
							if ($this->binContentAdmin()) {
								$del_link = $antrag->getLink() . "?" . AntiXSS::createToken("tag_del") . "=" . $tag->id;
								echo ' <a href="' . CHtml::encode($del_link) . '" class="del_link">del</a>';
							}
							echo '</li>';
						}
						echo '</ul>';
					}
					if ($this->aktuelleBenutzerIn()) {
						?>
						<a href="#" onclick="$('#tag_add_form').show(); $('#tag_add_link').hide(); return false;" id="tag_add_link">Neues Schlagwort</a>
						<form method="post" style="display: none;" id="tag_add_form">
							<label>Neues Schlagwort: <input name="tag_name" required></label>
							<button class="btn btn-primary" type="submit" name="<?=AntiXSS::createToken("tag_add")?>">Speichern</button>
						</form>
					<?
					}
				?></td>
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
						echo CHtml::link("Bezirksausschuss " . $antrag->ba_nr, $antrag->ba->getLink()) . " (" . CHtml::encode($antrag->ba->name) . ")<br>";
					} else {
						echo "Stadtrat<br>";
					}
					echo CHtml::encode(strip_tags($antrag->referat));
					?></td>
			</tr>
			<tr>
				<th>Antragsnummer:</th>
				<td><?= CHtml::encode($antrag->antrags_nr) ?></td>
			</tr>
			<?
			if ($antrag->gestellt_am > 0 && $antrag->gestellt_am == $antrag->registriert_am) {
				echo "<tr><th>Gestellt u. registriert: </th><td>" . CHtml::encode(RISTools::datumstring($antrag->gestellt_am)) . "</td></tr>\n";
			} else {
				if ($antrag->gestellt_am > 0) echo "<tr><th>Gestellt am:</th><td>" . CHtml::encode(RISTools::datumstring($antrag->gestellt_am)) . "</td></tr>\n";
				if ($antrag->registriert_am > 0) echo "<tr><th>Registriert am:</th><td>" . CHtml::encode(RISTools::datumstring($antrag->registriert_am)) . "</td></tr>\n";
			}
			if ($antrag->bearbeitungsfrist > 0) echo "<tr><th>Bearbeitungsfrist:</th><td>" . CHtml::encode(RISTools::datumstring($antrag->bearbeitungsfrist)) . "</td></tr>\n";
			if ($antrag->fristverlaengerung > 0) echo "<tr><th>Fristverlängerung:</th><td>" . CHtml::encode(RISTools::datumstring($antrag->fristverlaengerung)) . "</td></tr>\n";
			?>
			<tr>
				<th>Status:</th>
				<td><?
					echo CHtml::encode($antrag->status);
					if ($antrag->bearbeitung != "") echo " / ";
					echo CHtml::encode($antrag->bearbeitung);
					?></td>
			</tr>
			<tr>
				<th>Wahlperiode:</th>
				<td><?= CHtml::encode($antrag->wahlperiode) ?></td>
			</tr>
			<?
			$docs = $antrag->dokumente;
			usort($docs, function ($dok1, $dok2) {
				/**
				 * @var AntragDokument $dok1
				 * @var AntragDokument $dok2
				 */
				$ts1 = RISTools::date_iso2timestamp($dok1->getDate());
				$ts2 = RISTools::date_iso2timestamp($dok2->getDate());
				if ($ts1 > $ts2) return 1;
				if ($ts1 < $ts2) return -1;
				return 0;
			});
			zeile_anzeigen($docs, "Dokumente:", function ($dok) {
				/** @var AntragDokument $dok */
				echo CHtml::encode($dok->getDisplayDate()) . ": " . CHtml::link($dok->name, $dok->getLinkZumDokument());
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
				/** @var Antrag $element */
				echo CHtml::link($element->getName(true), $this->createUrl("antraege/anzeigen", array("id" => $element->id)));
			});

			zeile_anzeigen($antrag->vorlage2antraege, "Verbundene Stadtratsanträge:", function ($element) {
				/** @var Antrag $element */
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
					/** @var IRISItem $item */
					if (method_exists ($item , "getLinkZumDokument"))
						echo CHtml::link($item->getName(true), $item->getLinkZumDokument());
					else
						echo CHtml::link($item->getName(true), $item->getLink());
				});
			}
			?>

			</tbody>
		</table>
	</section>
</div>
<section class="col-md-4">

	<?
	$related = $antrag->errateThemenverwandteAntraege(7);
	?>
	<div class="well themenverwandt_liste" id="themenverwandt">

		<form method="POST" action="<?= Yii::app()->createUrl("antraege/anzeigen", array("id" => $antrag->id)) ?>" class="abo_button row_head"
			  style="min-height: 80px; text-align: center;">
			<?
			if ($antrag->vorgang && $antrag->vorgang->istAbonniert($this->aktuelleBenutzerIn())) {
				?>
				<button type="submit" name="<?= AntiXSS::createToken("deabonnieren") ?>" class="btn btn-success btn-raised btn-lg"><span class="glyphicon">@</span> Abonniert</button>
			<? } else { ?>
				<button type="submit" name="<?= AntiXSS::createToken("abonnieren") ?>" class="btn btn-info btn-raised btn-lg"><span class="glyphicon">@</span> Nicht abonniert</button>
			<? } ?>
		</form>
		<?
		if (count($related)) {
			?>
			<h2>Könnte themenverwandt sein</h2>


			<ul class="list-group">
				<?
				$this->renderPartial("related_list", array(
					"related" => $related,
					"narrow" => true,
				));
				?>
			</ul>

			<a href="<?= CHtml::encode(Yii::app()->createUrl("antraege/themenverwandte", array("id" => $antrag->id))) ?>" class="weitere">
				Weitere Themenverwandte <span class="glyphicon glyphicon-chevron-right"></span>
			</a>
		<? } ?>
	</div>
</section>
</section>
