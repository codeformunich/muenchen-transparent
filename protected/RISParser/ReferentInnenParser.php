<?php

class ReferentInnenParser
{
    private CurlBasedDownloader $curlBasedDownloader;

    public function __construct(?CurlBasedDownloader $curlBasedDownloader = null)
    {
        $this->curlBasedDownloader = $curlBasedDownloader ?: new CurlBasedDownloader();
    }

    private function createIfNotExistsReferat(ReferatData $data): Referat
    {
        /** @var Referat $referat */
        $referat = Referat::model()->findByPk($data->id);
        if (!$referat) {
            echo "Creating Referat: " . $data->name . " (" . $data->id . ")\n";
            $referat = new Referat();
            $referat->id = $data->id;
            $slugger = new \Symfony\Component\String\Slugger\AsciiSlugger();
            $referat->urlpart = $slugger->slug($data->name);
        } else {
            echo "Referat already exists: " . $data->name . "\n";
        }
        $referat->name = $data->name;
        $referat->save();

        return $referat;
    }

    public function createIfNotExistsReferentIn(ReferatData $data, Referat $referat): void
    {
        /** @var StadtraetIn $str */
        $str = StadtraetIn::model()->findByPk($data->referentInId);
        if ($str) {
            if ($str->name != $data->referentInName) {
                RISTools::report_ris_parser_error("ReferentIn Änderung", $str->name . " => " . $data->referentInName);
                $str->name = $data->referentInName;
                $str->save();
            }
            echo "ReferentIn exists: " . $str->name . "\n";
        } else {
            $str = new StadtraetIn();
            $str->name = $data->referentInName;
            $str->id = $data->referentInId;
            $str->referentIn = 1;
            $str->beruf = '';
            $str->bio = '';
            $str->beschreibung = '';
            $str->quellen = '';
            $str->save();
            echo "ReferentIn created: " . $str->name . "\n";
        }

        $gefunden = false;
        foreach ($str->stadtraetInnenReferate as $ref) {
            if ($ref->referat_id == $referat->id) {
                $gefunden = true;
            }
        }

        if (!$gefunden) {
            $zuo = new StadtraetInReferat();
            $zuo->referat_id = $referat->id;
            $zuo->stadtraetIn_id = $str->id;
            $zuo->save();
            RISTools::report_ris_parser_error("Neue ReferentInnen/Referat-Zuordnung", $referat->name . " / " . $str->name);
        }
    }

    public function parseAll(): void
    {
        $html = $this->curlBasedDownloader->loadUrl(RIS_BASE_URL . '/organisationseinheit/fachreferat/uebersicht');

        $parts = ReferatData::splitPage($html);
        for ($i = 0; $i < count($parts); $i++) {
            $parsed = ReferatData::parseFromHtml($parts[$i]);
            $referat = $this->createIfNotExistsReferat($parsed);
            $this->createIfNotExistsReferentIn($parsed, $referat);
        }
    }

    public function parseUpdate(): void
    {
        echo "Updates: ReferentInnen\n";
        $this->parseAll();
    }
}
