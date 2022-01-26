<?php

class StadtratGremiumParser
{
    public function parse($ris_id)
    {
        $ris_id = IntVal($ris_id);
        echo "- Gremium $ris_id\n";

        $html_details = RISTools::load_file(RIS_BASE_URL . "ris_gremien_detail.jsp?risid=" . $ris_id);

        $daten                         = new Gremium();
        $daten->id                     = $ris_id;
        $daten->datum_letzte_aenderung = new CDbExpression('NOW()');
        $daten->ba_nr                  = null;

        if (preg_match("/introheadline\">([^>]+)<\/h3/siU",         $html_details, $matches)) $daten->name       = $matches[1];
        if (preg_match("/rzel:.*detail_div\">([^>]*)<\//siU",       $html_details, $matches)) $daten->kuerzel    = $matches[1];
        if (preg_match("/Gremientyp:.*detail_div\">([^>]*)<\//siU", $html_details, $matches)) $daten->gremientyp = $matches[1];
        if (preg_match("/Referat:.*detail_div\">([^>]*)<\//siU",    $html_details, $matches)) $daten->referat    = $matches[1];

        foreach ($daten as $key => $val) {
            if (!($val === null || (is_object($val) && is_a($val, CDbExpression::class)))) {
                $daten[$key] = html_entity_decode(trim($val), ENT_COMPAT, "UTF-8");
            }
        }

        $aenderungen = "";

        /** @var Gremium $alter_eintrag */
        $alter_eintrag = Gremium::model()->findByPk($ris_id);
        $changed       = true;
        if ($alter_eintrag) {
            $changed = false;
            if ($alter_eintrag->name != $daten->name) $aenderungen .= "Name: " . $alter_eintrag->name . " => " . $daten->name . "\n";
            if ($alter_eintrag->kuerzel != $daten->kuerzel) $aenderungen .= "Kürzel: " . $alter_eintrag->kuerzel . " => " . $daten->kuerzel . "\n";
            if ($alter_eintrag->gremientyp != $daten->gremientyp) $aenderungen .= "Gremientyp: " . $alter_eintrag->gremientyp . " => " . $daten->gremientyp . "\n";
            if ($alter_eintrag->referat != $daten->referat) $aenderungen .= "Referat: " . $alter_eintrag->referat . " => " . $daten->referat . "\n";
            if ($aenderungen != "") $changed = true;
        }

        if ($changed) {
            if ($alter_eintrag) {
                $alter_eintrag->copyToHistory();
                $alter_eintrag->setAttributes($daten->getAttributes());
                if (!$alter_eintrag->save()) {
                    echo "Gremium 1";
                    var_dump($alter_eintrag->getErrors());
                    die("Fehler");
                }
                $daten = $alter_eintrag;
            } else {
                if (!$daten->save()) {
                    echo "Gremium 2";
                    var_dump($daten->getErrors());
                    die("Fehler");
                }
            }

            $aend              = new RISAenderung();
            $aend->ris_id      = $daten->id;
            $aend->ba_nr       = null;
            $aend->typ         = RISAenderung::TYP_STADTRAT_GREMIUM;
            $aend->datum       = new CDbExpression("NOW()");
            $aend->aenderungen = $aenderungen;
            $aend->save();
        }
    }
}
