<?php

class ReferentInnenParser extends RISParser
{

    public function parse($stadtraetIn_id)
    {
    }


    public function parseSeite($seite, $first)
    {
    }


    public function parseAlle()
    {
        $text = RISTools::load_file(RIS_BASE_URL . "ris_referenten_trefferliste.jsp?nav=1");
        $txt  = explode("<!-- ergebnisreihen -->", $text);
        $txt  = explode("<div class=\"ergebnisfuss\">", $txt[1]);

        preg_match_all("/ris_referenten_detail\.jsp\?risid=(?<id>[0-9]+)[\"'& ][^>]*>(?<name>[^<]+)<.*target=\"_blank\">(?<referat>[^<]+)</siU", $txt[0], $matches);
        for ($i = 0; $i < count($matches["name"]); $i++) {
            $name = trim($matches["name"][$i]);
            $name = str_replace("&nbsp;", " ", $name);
            $name = preg_replace("/ *(\n *)+/siu", "\n", $name);

            $x    = explode("\n", $name);
            $y    = explode(", ", $x[1]);
            $name = trim($x[0]) . " " . trim($y[1]) . " " . trim($y[0]);

            $id           = IntVal($matches["id"][$i]);
            $referat_name = $matches["referat"][$i];

            /** @var StadtraetIn $str */
            $str = StadtraetIn::model()->findByPk($id);
            if ($str) {
                if ($str->name != $name) {
                    RISTools::report_ris_parser_error("ReferentIn Änderung", $str->name . " => " . $name);
                    $str->name = $name;
                    $str->save();
                }
            } else {
                $str             = new StadtraetIn();
                $str->name       = $name;
                $str->id         = $id;
                $str->referentIn = 1;
                $str->save();
            }

            /** @var Referat $referat */
            $referat = Referat::model()->findByAttributes(["name" => $referat_name]);
            if (!$referat) {
                RISTools::report_ris_parser_error("Referat nicht gefunden", $referat_name);
                return;
            }

            $gefunden = false;
            foreach ($str->stadtraetInnenReferate as $ref) if ($ref->referat_id == $referat->id) $gefunden = true;

            if (!$gefunden) {
                $zuo                 = new StadtraetInReferat();
                $zuo->referat_id     = $referat->id;
                $zuo->stadtraetIn_id = $str->id;
                $zuo->save();
                RISTools::report_ris_parser_error("Neue ReferentInnen/Referat-Zuordnung", $referat_name . " / " . $str->name);
            }
        }

    }

    public function parseUpdate()
    {
        echo "Updates: ReferentInnen\n";
        $this->parseAlle();
    }

    public function parseQuickUpdate()
    {

    }
}
