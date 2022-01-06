<?php

declare(strict_types=1);

class CalendarAgendaUpdater
{
    /** @var Tagesordnungspunkt[] */
    private array $unmatchedOldItems;

    private StadtratsvorlageParser $stadtratsvorlageParser;

    public function __construct(StadtratsvorlageParser $stadtratsvorlageParser)
    {
        $this->stadtratsvorlageParser = $stadtratsvorlageParser;
    }

    public function setOldItems(array $oldItems): void
    {
        $this->unmatchedOldItems = $oldItems;
    }

    private function isEquivalentAgendaItem(Tagesordnungspunkt $oldItem, CalendarAgendaItem $newItem): bool
    {
        if ($oldItem->top_id > 0 || $newItem->id > 0) {
            // If an agenda item has an ID, we are strict about the matching
            return intval($oldItem->top_id) === intval($newItem->id);
        }

        if ($oldItem->antrag_id > 0) {
            // Note: if the old item has no antrag_id, but the new one does, matching via title should still be possible
            return count($newItem->vorlagenIds) > 0 && intval($oldItem->antrag_id) === $newItem->vorlagenIds[0];
        }

        return $oldItem->top_betreff === $newItem->title;
    }

    private function findAndRemoveMatchingOldItem(CalendarAgendaItem $newItem): ?Tagesordnungspunkt
    {
        for ($i = 0; $i < count($this->unmatchedOldItems); $i++) {
            $oldItem = $this->unmatchedOldItems[$i];
            if ($this->isEquivalentAgendaItem($oldItem, $newItem)) {
                array_splice($this->unmatchedOldItems, $i, 1); // Remove this object

                return $oldItem;
            }
        }

        return null;
    }

    private function getVorlagenId(CalendarAgendaItem $item): ?int
    {
        if (count($item->vorlagenIds) > 1) {
            var_dump($item);
            die("More than one Vorlage");
        }
        foreach ($item->vorlagenIds as $vorlagenId) {
            $vorlage = Antrag::model()->findByPk($vorlagenId);
            if (!$vorlage) {
                echo "Creating: $vorlagenId\n";
                $this->stadtratsvorlageParser->parse($vorlagenId);
                $vorlage = Antrag::model()->findByPk($vorlagenId);
            }

            return $vorlage ? $vorlagenId : null;
        }

        return null;
    }

    private function updateAgendaItem(Termin $agenda, CalendarAgendaItem $newItem): string
    {
        $top = $this->findAndRemoveMatchingOldItem($newItem);
        if ($top) {
            $oldDataCopy = new Tagesordnungspunkt();
            $oldDataCopy->setAttributes($top->getAttributes(), false);
        } else {
            $top = new Tagesordnungspunkt();
            $top->datum_letzte_aenderung = new CDbExpression("NOW()");
            $oldDataCopy = null;
        }

        $vorlagenId = $this->getVorlagenId($newItem);

        $top->sitzungstermin_id = $agenda->id;
        $top->sitzungstermin_datum = substr($agenda->termin, 0, 10);
        $top->top_pos = $newItem->position;
        $top->top_id = $newItem->id;
        $top->top_nr = $newItem->topNr;
        $top->antrag_id = $vorlagenId;
        $top->top_ueberschrift = ($newItem->isHeading ? 1 : 0);
        $top->status = ($newItem->public ? '' : Tagesordnungspunkt::STATUS_NONPUBLIC);
        $top->entscheidung = $parsedItem->decision ?? $newItem->disclosure;
        $top->top_betreff = $newItem->title;
        $top->gremium_id = $agenda->gremium_id;
        $top->gremium_name = $agenda->gremium->name;
        $top->beschluss_text = "";

        $changesStr = '';
        if ($oldDataCopy) {
            $agendaChanges = '';
            if ($oldDataCopy->sitzungstermin_id != $top->sitzungstermin_id) $agendaChanges .= "Sitzung geändert: " . $oldDataCopy->sitzungstermin_id . " => " . $top->sitzungstermin_id . "\n";
            if ($oldDataCopy->sitzungstermin_datum != $top->sitzungstermin_datum) $agendaChanges .= "Sitzungstermin geändert: " . $oldDataCopy->sitzungstermin_datum . " => " . $top->sitzungstermin_datum . "\n";
            if ($oldDataCopy->top_nr != $top->top_nr) $agendaChanges .= "TOP geändert: " . $oldDataCopy->top_nr . " => " . $top->top_nr . "\n";
            if ($oldDataCopy->top_ueberschrift != $top->top_ueberschrift) $agendaChanges .= "Bereich geändert: " . $oldDataCopy->top_ueberschrift . " => " . $top->top_ueberschrift . "\n";
            if ($oldDataCopy->top_betreff != $top->top_betreff) $agendaChanges .= "Betreff geändert: " . $oldDataCopy->top_betreff . " => " . $top->top_betreff . "\n";
            if ($oldDataCopy->antrag_id != $top->antrag_id) $agendaChanges .= "Antrag geändert: " . $oldDataCopy->antrag_id . " => " . $top->antrag_id . "\n";
            if ($oldDataCopy->gremium_id != $top->gremium_id) $agendaChanges .= "Gremium geändert: " . $oldDataCopy->gremium_id . " => " . $top->gremium_id . "\n";
            if ($oldDataCopy->gremium_name != $top->gremium_name) $agendaChanges .= "Gremium geändert: " . $oldDataCopy->gremium_name . " => " . $top->gremium_name . "\n";
            if ($oldDataCopy->entscheidung != $top->entscheidung) $agendaChanges .= "Entscheidung: " . $oldDataCopy->entscheidung . " => " . $top->entscheidung . "\n";
            if ($oldDataCopy->beschluss_text != $top->beschluss_text) $agendaChanges .= "Beschluss: " . $oldDataCopy->beschluss_text . " => " . $top->beschluss_text . "\n";

            if ($agendaChanges !== '') {
                $aend = new RISAenderung();
                $aend->ris_id = $oldDataCopy->id;
                $aend->ba_nr = null;
                $aend->typ = RISAenderung::$TYP_STADTRAT_ERGEBNIS;
                $aend->datum = new CDbExpression("NOW()");
                $aend->aenderungen = $agendaChanges;
                $aend->save();

                $oldDataCopy->copyToHistory();

                $changesStr = "TOP geändert: " . $top->top_betreff . "\n   " . str_replace("\n", "\n   ", $agendaChanges) . "\n";

                $top->datum_letzte_aenderung = new CDbExpression("NOW()");
            }
        } else {
            $changesStr = "Neuer TOP: " . $top->top_nr . " - " . $top->top_betreff . "\n";
        }

        /*
             * @TODO Beschlüsse e.g. in https://risi.muenchen.de/risi/sitzung/detail/5656928/tagesordnung/oeffentlich
                // $aenderungen .= Dokument::create_if_necessary(Dokument::$TYP_STADTRAT_BESCHLUSS, $top, ["url" => $matches2["url"][0], "name" => $matches2["title"][0], "name_title" => ""]);
                / @var Dokument $dok /
                $dok = Dokument::model()->findByAttributes(["tagesordnungspunkt_id" => $top->id, "url" => $matches2["url"][0], "name" => $matches2["title"][0]]);
                if ($dok && $dok->tagesordnungspunkt_id != $top->id) {
                    echo "Korrgiere ID\n";
                    $dok->tagesordnungspunkt_id = $top->id;
                    $dok->save(false);
                }
            */

        if (!is_null($vorlagenId)) {
            // @TODO Find out if there is still a "Beschlusstext" stored for the agenda
        }

        $top->save();

        return $changesStr;
    }

    /**
     * @param CalendarAgendaItem[] $newItems
     *
     * @return string - a string describing the changes made. Empty string if no changes
     */
    public function updateAgendaToNewItems(Termin $agenda, array $newItems): string
    {
        $changesStr = "";
        foreach ($newItems as $parsedItem) {
            $changes = $this->updateAgendaItem($agenda, $parsedItem);
            if ($changes !== '') {
                $changesStr .= $changes . "\n";
            }
        }

        foreach ($this->unmatchedOldItems as $top) {
            $changesStr .= "TOP entfernt: " . $top->top_nr . ":" . $top->top_betreff . "\n";
            try {
                $top->delete();
            } catch (CDbException $e) {
                $str = "Vermutlich verwaiste Dokumente (war zuvor: \"" . $top->getName() . "\" in " . $agenda->getLink() . ":\n";
                /** @var Dokument[] $doks */
                $doks = Dokument::model()->findAllByAttributes(["tagesordnungspunkt_id" => $top->id]);
                foreach ($doks as $dok) {
                    $dok->tagesordnungspunkt_id = null;
                    $dok->save(false);
                    $str .= $dok->getOriginalLink() . "\n";
                }
                RISTools::send_email(Yii::app()->params["adminEmail"], "StadtratTermin Verwaist", $str, null, "system");
                $top->delete();
            }
        }

        return $changesStr;
    }
}
