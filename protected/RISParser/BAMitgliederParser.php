<?php

class BAMitgliederParser extends RISParser
{

    public function parse($ba_nr)
    {
        $ba_nr = IntVal($ba_nr);

        if (SITE_CALL_MODE != "cron") echo "- BA $ba_nr\n";
        /** @var Bezirksausschuss $ba */
        $ba = Bezirksausschuss::model()->findByPk($ba_nr);

        $ba_details = RISTools::load_file("http://www.ris-muenchen.de/RII/BA-RII/ba_bezirksausschuesse_details.jsp?Id=" . $ba->ris_id);

        preg_match("/Wahlperiode.*detail_div\">(?<wahlperiode>[^<]*)</siuU", $ba_details, $matches);
        $wahlperiode = $matches["wahlperiode"];

        $x = explode('<!-- tabellenkopf -->', $ba_details);
        $x = explode('<!-- seitenfuss -->', $x[1]);

        preg_match_all("/ba_mitglieder_details_mitgliedschaft\.jsp\?Id=(?<mitglied_id>[0-9]+)&amp;Wahlperiode=(?<wahlperiode>[0-9]+)[\"'& ]>(?<name>[^<]*)<.*tdborder\">(?<mitgliedschaft>[^<]*)<\/td>.*tdborder[^>]*>(?<fraktion>[^<]*) *<\/td>.*notdborder[^>]*>(?<funktion>[^<]*) *<\/td.*<\/tr/siU", $x[0], $matches);
        for ($i = 0; $i < count($matches[1]); $i++) {
            $fraktion_name = trim(html_entity_decode($matches["fraktion"][$i]));
            $name          = str_replace("&nbsp;", " ", $matches["name"][$i]);
            $name          = trim(str_replace(array("Herr", "Frau"), array(" ", " "), $name));

            if ($fraktion_name == "")
                $fraktion_name = "Parteifrei";

            /** @var StadtraetIn $strIn */
            $strIn = StadtraetIn::model()->findByPk($matches["mitglied_id"][$i]);
            if (!$strIn) {
                echo "Neu anlegen: " . $matches["mitglied_id"][$i] . " - " . $name . " (" . $fraktion_name . ")\n";

                $strIn             = new StadtraetIn();
                $strIn->name       = $name;
                $strIn->id         = $matches["mitglied_id"][$i];
                $strIn->referentIn = 0;

                $x                  = explode(".", $matches["mitgliedschaft"][$i]);
                $strIn->gewaehlt_am = $x[2] . "-" . $x[1] . "-" . $x[0];
                $strIn->save();
            }

            /** @var Fraktion|null $fraktion */
            $fraktion = Fraktion::model()->findByAttributes(array("ba_nr" => $ba_nr, "name" => $fraktion_name));
            if (!$fraktion) {
                echo "Lege an: " . $fraktion_name . "\n";
                $min = Yii::app()->db->createCommand()->select("MIN(id)")->from("fraktionen")->queryColumn()[0] - 1;
                if ($min > 0) $min = -1;
                $fraktion        = new Fraktion();
                $fraktion->id    = $min;
                $fraktion->name  = $fraktion_name;
                $fraktion->ba_nr = $ba_nr;
                $fraktion->save();
            }

            $gefunden = false;
            foreach ($strIn->stadtraetInnenFraktionen as $strfrakt) if ($strfrakt->fraktion_id == $fraktion->id) {
                $gefunden = true;
                $von_pre = $strfrakt->datum_von;
                $bis_pre = $strfrakt->datum_bis;
                if (preg_match("/^von (?<von_tag>[0-9]+)\\.(?<von_monat>[0-9]+)\\.(?<von_jahr>[0-9]+) bis (?<bis_tag>[0-9]+)\\.(?<bis_monat>[0-9]+)\\.(?<bis_jahr>[0-9]+)$/", $matches["mitgliedschaft"][$i], $mitgliedschaft_matches)) {
                    $strfrakt->datum_von = $mitgliedschaft_matches["von_jahr"] . "-" . $mitgliedschaft_matches["von_monat"] . "-" . $mitgliedschaft_matches["von_tag"];
                    $strfrakt->datum_bis = $mitgliedschaft_matches["bis_jahr"] . "-" . $mitgliedschaft_matches["bis_monat"] . "-" . $mitgliedschaft_matches["bis_tag"];
                } elseif (preg_match("/^seit (?<von_tag>[0-9]+)\\.(?<von_monat>[0-9]+)\\.(?<von_jahr>[0-9]+)$/", $matches["mitgliedschaft"][$i], $mitgliedschaft_matches)) {
                    $strfrakt->datum_von = $mitgliedschaft_matches["von_jahr"] . "-" . $mitgliedschaft_matches["von_monat"] . "-" . $mitgliedschaft_matches["von_tag"];
                    $strfrakt->datum_bis = null;
                }
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

                if (preg_match("/^von (?<von_tag>[0-9]+)\\.(?<von_monat>[0-9]+)\\.(?<von_jahr>[0-9]+) bis (?<bis_tag>[0-9]+)\\.(?<bis_monat>[0-9]+)\\.(?<bis_jahr>[0-9]+)$/", $matches["mitgliedschaft"][$i], $mitgliedschaft_matches)) {
                    $strfrakt->datum_von = $mitgliedschaft_matches["von_jahr"] . "-" . $mitgliedschaft_matches["von_monat"] . "-" . $mitgliedschaft_matches["von_tag"];
                    $strfrakt->datum_bis = $mitgliedschaft_matches["bis_jahr"] . "-" . $mitgliedschaft_matches["bis_monat"] . "-" . $mitgliedschaft_matches["bis_tag"];
                } elseif (preg_match("/^seit (?<von_tag>[0-9]+)\\.(?<von_monat>[0-9]+)\\.(?<von_jahr>[0-9]+)$/", $matches["mitgliedschaft"][$i], $mitgliedschaft_matches)) {
                    $strfrakt->datum_von = $mitgliedschaft_matches["von_jahr"] . "-" . $mitgliedschaft_matches["von_monat"] . "-" . $mitgliedschaft_matches["von_tag"];
                    $strfrakt->datum_bis = null;
                }
                $strfrakt->save();
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
