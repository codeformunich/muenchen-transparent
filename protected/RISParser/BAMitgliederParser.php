<?php

class BAMitgliederParser extends RISParser
{

    private function parseBaGremienListe(string $url)
    {
        $html = RISTools::load_file(RIS_BA_BASE_URL . "ba_gremien.jsp?selWahlperiode=3184784&selBA=" . $url);

    }

    private static function parseSeitVonBisStr(string $str): array
    {
        $von = null;
        $bis = null;
        if (preg_match("/^von (?<von_tag>[0-9]+)\\.(?<von_monat>[0-9]+)\\.(?<von_jahr>[0-9]+) bis (?<bis_tag>[0-9]+)\\.(?<bis_monat>[0-9]+)\\.(?<bis_jahr>[0-9]+)$/", $str, $matches)) {
            $von = $matches["von_jahr"] . "-" . $matches["von_monat"] . "-" . $matches["von_tag"];
            $bis = $matches["bis_jahr"] . "-" . $matches["bis_monat"] . "-" . $matches["bis_tag"];
        } elseif (preg_match("/^seit (?<von_tag>[0-9]+)\\.(?<von_monat>[0-9]+)\\.(?<von_jahr>[0-9]+)$/", $str, $matches)) {
            $von = $matches["von_jahr"] . "-" . $matches["von_monat"] . "-" . $matches["von_tag"];
        }
        return ["von" => $von, "bis" => $bis];
    }

    public function parse($ba_nr)
    {
        $ba_nr = IntVal($ba_nr);

        if (SITE_CALL_MODE != "cron") echo "- BA $ba_nr\n";
        /** @var Bezirksausschuss $ba */
        $ba = Bezirksausschuss::model()->findByPk($ba_nr);

        $ba_details = RISTools::load_file(RIS_BA_BASE_URL . "ba_bezirksausschuesse_details.jsp?Id=" . $ba->ris_id);

        preg_match("/Wahlperiode.*detail_div\">(?<wahlperiode>[^<]*)</siuU", $ba_details, $matches);
        $wahlperiode = $matches["wahlperiode"];

        $x = explode('<!-- tabellenkopf -->', $ba_details);
        $x = explode('<!-- seitenfuss -->', $x[1]);

        $gefundene_fraktionen = [];

        preg_match_all("/ba_mitglieder_details_mitgliedschaft\.jsp\?Id=(?<mitglied_id>[0-9]+)&amp;Wahlperiode=(?<wahlperiode>[0-9]+)[\"'& ]>(?<name>[^<]*)<.*tdborder\">(?<mitgliedschaft>[^<]*)<\/td>.*tdborder[^>]*>(?<fraktion>[^<]*) *<\/td>.*notdborder[^>]*>(?<funktion>[^<]*) *<\/td.*<\/tr/siU", $x[0], $matches);
        for ($i = 0; $i < count($matches[1]); $i++) {
            $fraktion_name = trim(html_entity_decode($matches["fraktion"][$i]));
            $name          = str_replace("&nbsp;", " ", $matches["name"][$i]);
            $name          = trim(str_replace(["Herr", "Frau"], [" ", " "], $name));

            if ($fraktion_name == "")
                $fraktion_name = "Parteifrei";

            /** @var StadtraetIn $strIn */
            $strIn = StadtraetIn::model()->findByPk($matches["mitglied_id"][$i]);
            if (!$strIn) {
                echo "Neu anlegen: " . $matches["mitglied_id"][$i] . " - " . $name . " (" . $fraktion_name . ")\n";

                $strIn               = new StadtraetIn();
                $strIn->name         = $name;
                $strIn->id           = $matches["mitglied_id"][$i];
                $strIn->referentIn   = 0;
                $strIn->bio          = "";
                $strIn->web          = "";
                $strIn->beruf        = "";
                $strIn->beschreibung = "";
                $strIn->quellen      = "";
                $strIn->gewaehlt_am  = static::parseSeitVonBisStr($matches["mitgliedschaft"][$i])["von"];
                $strIn->save();
            }

            /** @var Fraktion|null $fraktion */
            $fraktion = Fraktion::model()->findByAttributes(["ba_nr" => $ba_nr, "name" => $fraktion_name]);
            if (!$fraktion) {
                echo "Lege an: " . $fraktion_name . "\n";
                $min = Yii::app()->db->createCommand()->select("MIN(id)")->from("fraktionen")->queryColumn()[0] - 1;
                if ($min > 0) $min = -1;
                $fraktion            = new Fraktion();
                $fraktion->id        = $min;
                $fraktion->name      = $fraktion_name;
                $fraktion->ba_nr     = $ba_nr;
                $fraktion->website   = "";
                $fraktion->save();
            }

            $gefunden = false;
            foreach ($strIn->stadtraetInnenFraktionen as $strfrakt) if ($strfrakt->fraktion_id == $fraktion->id) {
                $gefunden            = true;
                $von_pre             = $strfrakt->datum_von;
                $bis_pre             = $strfrakt->datum_bis;
                $strfrakt->datum_von = static::parseSeitVonBisStr($matches["mitgliedschaft"][$i])["von"];
                $strfrakt->datum_bis = static::parseSeitVonBisStr($matches["mitgliedschaft"][$i])["bis"];
                if ($von_pre != $strfrakt->datum_von || $bis_pre != $strfrakt->datum_bis) {
                    $strfrakt->save();
                    echo $strIn->getName() . ": " . $von_pre . "/" . $bis_pre . " => " . $strfrakt->datum_von . "/" . $strfrakt->datum_bis . "\n";
                }
            }
            if (!$gefunden) {
                $strfrakt                 = new StadtraetInFraktion();
                $strfrakt->fraktion_id    = $fraktion->id;
                $strfrakt->stadtraetIn_id = $strIn->id;
                $strfrakt->wahlperiode    = $wahlperiode;
                $strfrakt->mitgliedschaft = $matches["mitgliedschaft"][$i];
                $strfrakt->datum_von      = static::parseSeitVonBisStr($matches["mitgliedschaft"][$i])["von"];
                $strfrakt->datum_bis      = static::parseSeitVonBisStr($matches["mitgliedschaft"][$i])["bis"];
                $strfrakt->save();
            }
            if (!isset($gefundene_fraktionen[$matches["mitglied_id"][$i]])) $gefundene_fraktionen[$matches["mitglied_id"][$i]] = [];
            $gefundene_fraktionen[$matches["mitglied_id"][$i]][] = $fraktion->id;
        }

        foreach ($gefundene_fraktionen as $strIn => $fraktionen) {
            //SELECT a.* FROM `fraktionen` a JOIN stadtraetInnen_fraktionen b ON a.id = b.fraktion_id WHERE b.stadtraetIn_id = 3314069 AND a.ba_nr = 18 AND b.fraktion_id NOT IN (-88)
            $sql = 'DELETE FROM b USING `fraktionen` a JOIN `stadtraetInnen_fraktionen` b ON a.id = b.fraktion_id WHERE ';
            $frakts = implode(", ", array_map('IntVal', $fraktionen));
            $sql .= 'b.stadtraetIn_id = ' . IntVal($strIn) . ' AND a.ba_nr = ' . IntVal($ba_nr) . ' AND b.fraktion_id NOT IN (' . $frakts . ')';
            if (Yii::app()->db->createCommand($sql)->execute() > 0) {
                echo 'Fraktionen gelöscht bei: ' . $strIn . "\n";
            }
        }

        $stadtraetInnenIds = array_map("IntVal", array_keys($gefundene_fraktionen));
        if (count($stadtraetInnenIds) > 0) {
            $sql = 'DELETE FROM b USING stadtraetInnen a JOIN stadtraetInnen_fraktionen b ON a.id = b.stadtraetIn_id JOIN fraktionen c ON b.fraktion_id = c.id ' .
                   'WHERE c.ba_nr = ' . IntVal($ba_nr) . ' AND a.id NOT IN(' . implode(", ", $stadtraetInnenIds) . ')';
            if (Yii::app()->db->createCommand($sql)->execute() > 0) {
                echo "Verwaiste Fraktions-Zuordnungen gelöscht\n";
            }
        }
    }

    public function parseSeite($seite, $first)
    {
        $this->parseAlle();
    }

    public function parseAlle()
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->parse($i);
        }
    }

    public function parseUpdate()
    {
        $this->parseAlle();
    }

    public function parseQuickUpdate()
    {

    }
}
