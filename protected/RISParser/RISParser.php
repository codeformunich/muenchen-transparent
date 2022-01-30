<?php

abstract class RISParser
{
    private const MONTHS = [
        "januar" => 1,
        "februar" => 2,
        "märz" => 3,
        "april" => 4,
        "mai" => 5,
        "juni" => 6,
        "juli" => 7,
        "august" => 8,
        "september" => 9,
        "oktober" => 10,
        "november" => 11,
        "dezember" => 12,
    ];

    public abstract function parse(int $id): mixed;

    public abstract function parseAll(): void;

    public abstract function parseUpdate(): void;

    public abstract function parseQuickUpdate(): void;

    public static function text_simple_clean(string $text): string
    {
        $text = trim($text);
        $text = preg_replace("/<br ?\/?>/siU", "\n", $text);
        $text = str_replace("\n\n", "\n", $text);
        $text = str_replace("&nbsp;", " ", $text);
        $text = html_entity_decode($text, ENT_COMPAT, "UTF-8");
        return trim($text);
    }

    public static function date_de2mysql(string $dat, ?string $fallback = null): ?string
    {
        $x = explode(".", trim($dat));
        if (count($x) != 3) return $fallback;
        if (strlen($x[0]) == 1) $x[0] = "0" . $x[0];
        if (strlen($x[1]) == 1) $x[1] = "0" . $x[1];
        if (strlen($x[2]) == 2) $x[2] = "20" . $x[2];
        return $x[2] . "-" . $x[1] . "-" . $x[0];
    }

    public static function parseGermanLongDate(string $date): DateTime
    {
        preg_match('/(?<day>\d+)\. (?<month>[a-zäöüß]+) (?<year>\d{4}), (?<hour>\d+):(?<minute>\d+)/siu', $date, $match);

        return (new \DateTime())
            ->setDate(intval($match['year']), static::MONTHS[mb_strtolower($match['month'])], intval($match['day']))
            ->setTime(intval($match['hour']), intval($match['minute']), 0, 0);
    }

    /** @param int [] */
    public function parseIDs(array $ids): void
    {
        foreach ($ids as $id) $this->parse($id);
    }

    protected array $deletedIds = [];

    protected function deleteAntrag(int $id, string $changeType): void
    {
        $this->deletedIds[] = $id;
        if (count($this->deletedIds) > 10) {
            RISTools::report_ris_parser_error("Deleting more than 10 documents at once - aborting", $changeType . "\n" . print_r($this->deletedIds, true));
            echo "Deleted more than 10 " . $changeType . ": aborting\n";
            die();
        }

        echo "Lösche " . $changeType . ": $id\n";

        /** @var Antrag $document */
        $document = Antrag::model()->findByPk($id);
        $document->copyToHistory();

        foreach ($document->dokumente as $dok) {
            echo "- Dokument " . $dok->id . ": Antrag " . $dok->antrag_id . " => null\n";
            $dok->antrag_id = null;
            $dok->save();
        }
        foreach ($document->ergebnisse as $erg) {
            echo "- Ergebnis " . $erg->id . ": Antrag " . $erg->antrag_id . " => null\n";
            $erg->antrag_id = null;
            $erg->save();
        }
        Yii::app()->db->createCommand("UPDATE tagesordnungspunkte_history SET antrag_id = NULL WHERE antrag_id = " . IntVal($id))->execute();
        Yii::app()->db->createCommand("UPDATE tagesordnungspunkte SET antrag_id = NULL WHERE antrag_id = " . IntVal($id))->execute();
        Yii::app()->db->createCommand("DELETE FROM antraege_orte WHERE antrag_id = " . IntVal($id))->execute();
        Yii::app()->db->createCommand("DELETE FROM antraege_vorlagen WHERE antrag1 = " . IntVal($id))->execute();

        if (!$document->delete()) {
            RISTools::report_ris_parser_error("Could not delete Antrag", print_r($document->getErrors(), true));
            die("Fehler");
        }
        $aend              = new RISAenderung();
        $aend->ris_id      = $document->id;
        $aend->ba_nr       = NULL;
        $aend->typ         = $changeType;
        $aend->datum       = new CDbExpression("NOW()");
        $aend->aenderungen = 'Gelöscht';
        $aend->save();
    }
}
