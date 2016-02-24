<?php

class StadtratsfraktionParser
{

    public function parse($fraktion_id, $wahlperiode_id)
    {

        $fraktion_id    = IntVal($fraktion_id);
        $wahlperiode_id = IntVal($wahlperiode_id);

        if (SITE_CALL_MODE != "cron") echo "- Fraktion $fraktion_id\n";

        $html_details = RISTools::load_file("http://www.ris-muenchen.de/RII/RII/ris_fraktionen_detail.jsp?risid=${fraktion_id}&periodeid=${wahlperiode_id}");

        $daten     = new Fraktion();
        $daten->id = $fraktion_id;

        if (preg_match("/introheadline\">(.*)<\/h3/siU", $html_details, $matches)) {
            var_dump($matches);
            $daten->name = trim(str_replace("&nbsp;", " ", $matches[1]));
        }

        $aenderungen = "";

        /** @var Fraktion $alter_eintrag */
        $alter_eintrag = Fraktion::model()->findByPk($fraktion_id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($alter_eintrag->name != $daten->name) $aenderungen .= "Name: " . $alter_eintrag->name . " => " . $daten->name . "\n";
            if ($aenderungen != "") $changed = true;
        }

        if ($changed) {
            if ($aenderungen == "") $aenderungen = "Neu angelegt\n";
        }

        if ($alter_eintrag) {
            $alter_eintrag->setAttributes($daten->getAttributes(), false);
            if (!$alter_eintrag->save()) {
                echo "Fraktion 1\n";
                var_dump($alter_eintrag->getErrors());
                die("Fehler");
            }
            $daten = $alter_eintrag;
        } else {
            if (!$daten->save()) {
                echo "Fraktion 2\n";
                var_dump($daten->getErrors());
                die("Fehler");
            }
        }

        if ($aenderungen != "") echo "Fraktion $fraktion_id: Verändert: " . $aenderungen . "\n";

        if ($aenderungen != "") {
            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = null;
            $aend->typ         = RISAenderung::$TYP_STADTRAT_FRAKTION;
            $aend->datum       = new DbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();
        }

    }

}