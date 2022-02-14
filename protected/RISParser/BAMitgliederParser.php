<?php

class BAMitgliederParser extends RISParser
{
    private BrowserBasedDowloader $browserBasedDowloader;
    private CurlBasedDownloader $curlBasedDownloader;

    public function __construct(?BrowserBasedDowloader $browserBasedDowloader = null, ?CurlBasedDownloader $curlBasedDownloader = null)
    {
        $this->browserBasedDowloader = $browserBasedDowloader ?: new BrowserBasedDowloader();
        $this->curlBasedDownloader = $curlBasedDownloader ?: new CurlBasedDownloader();
    }

    public function parse(int $id): StadtraetIn
    {
        $htmlFraktionen = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'person/detail/' . $id . '?tab=fraktionen', false, true);
        $htmlBas = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'person/detail/' . $id . '?tab=mitgliedschaften', false, true);
        $htmlAusschuesse = $this->curlBasedDownloader->loadUrl(RIS_URL_PREFIX . 'person/detail/' . $id . '?tab=baausschuesse', false, true);

        $parsed = BAMitgliederData::parseFromHtml($htmlBas, $htmlFraktionen, $htmlAusschuesse, $id);

        /** @var StadtraetIn $strIn */
        $strIn = StadtraetIn::model()->findByPk($parsed->id);
        if (!$strIn) {
            echo "Neu anlegen: " . $parsed->id . " - " . $parsed->name . " (BA " . $parsed->baNr . ")\n";

            $strIn               = new StadtraetIn();
            $strIn->name         = $parsed->name;
            $strIn->id           = $parsed->id;
            $strIn->referentIn   = 0;
            $strIn->bio          = "";
            $strIn->web          = "";
            $strIn->beruf        = "";
            $strIn->beschreibung = "";
            $strIn->quellen      = "";
            $strIn->gewaehlt_am  = null;
            $strIn->save();
        }

        $this->setFraktionsmitgliedschaften($strIn, $parsed);


        // $sql = 'DELETE FROM b USING stadtraetInnen a JOIN stadtraetInnen_fraktionen b ON a.id = b.stadtraetIn_id JOIN fraktionen c ON b.fraktion_id = c.id ' .
        //                   'WHERE c.ba_nr = ' . IntVal($ba_nr) . ' AND a.id NOT IN(' . implode(", ", $stadtraetInnenIds) . ')';

        return $strIn;
    }

    private function setFraktionsmitgliedschaften(StadtraetIn $strIn, BAMitgliederData $data): void
    {
        if (count($data->fraktionsMitgliedschaften) === 0) {
            return;
        }
        // @TODO Support multiple memberships
        $mitgliedschaft = array_shift($data->fraktionsMitgliedschaften);

        /** @var Fraktion|null $fraktion */
        $fraktion = Fraktion::model()->findByAttributes(["ba_nr" => $data->baNr, "name" => $mitgliedschaft->gremiumName]);
        if (!$fraktion) {
            echo "Lege an: " . $mitgliedschaft->gremiumName . "\n";
            $min = Yii::app()->db->createCommand()->select("MIN(id)")->from("fraktionen")->queryColumn()[0] - 1;
            if ($min > 0) $min = -1;
            $fraktion = new Fraktion();
            $fraktion->id = $min;
            $fraktion->name = $mitgliedschaft->gremiumName;
            $fraktion->ba_nr = $data->baNr;
            $fraktion->website = "";
            $fraktion->save();
        }

        $gefunden = false;
        foreach ($strIn->stadtraetInnenFraktionen as $strfrakt) if ($strfrakt->fraktion_id == $fraktion->id) {
            $gefunden = true;
            $von_pre = $strfrakt->datum_von;
            $bis_pre = $strfrakt->datum_bis;
            $strfrakt->datum_von = $mitgliedschaft->seit?->format('Y-m-d');
            $strfrakt->datum_bis = $mitgliedschaft->bis?->format('Y-m-d');
            $strfrakt->mitgliedschaft = $strfrakt->datum_von . ' - ' . $strfrakt->datum_bis;
            if ($von_pre != $strfrakt->datum_von || $bis_pre != $strfrakt->datum_bis) {
                echo $strIn->getName() . ": " . $von_pre . "/" . $bis_pre . " => " . $strfrakt->datum_von . "/" . $strfrakt->datum_bis . "\n";
                $strfrakt->save();
            }
        }
        if (!$gefunden) {
            $strfrakt = new StadtraetInFraktion();
            $strfrakt->fraktion_id = $fraktion->id;
            $strfrakt->stadtraetIn_id = $strIn->id;
            $strfrakt->wahlperiode = $mitgliedschaft->wahlperiode;
            $strfrakt->datum_von = $mitgliedschaft->seit?->format('Y-m-d');
            $strfrakt->datum_bis = $mitgliedschaft->bis?->format('Y-m-d');
            $strfrakt->mitgliedschaft = $strfrakt->datum_von . ' - ' . $strfrakt->datum_bis;
            $strfrakt->save();
        }

        //SELECT a.* FROM `fraktionen` a JOIN stadtraetInnen_fraktionen b ON a.id = b.fraktion_id WHERE b.stadtraetIn_id = 3314069 AND a.ba_nr = 18 AND b.fraktion_id NOT IN (-88)
        $gefundene_fraktionen = [$fraktion->id];
        $frakts = implode(", ", array_map('IntVal', $gefundene_fraktionen));
        $sql = 'DELETE FROM b USING `fraktionen` a JOIN `stadtraetInnen_fraktionen` b ON a.id = b.fraktion_id WHERE ';
        $sql .= 'b.stadtraetIn_id = ' . IntVal($data->id) . ' AND a.ba_nr = ' . IntVal($data->baNr) . ' AND b.fraktion_id NOT IN (' . $frakts . ')';
        if (Yii::app()->db->createCommand($sql)->execute() > 0) {
            echo 'Fraktionen gelöscht bei: ' . $data->baNr . "\n";
        }
    }

    public function parseAll(): void
    {
        $html = $this->browserBasedDowloader->downloadPersonList(BrowserBasedDowloader::PERSON_TYPE_BA_MITGLIEDER);

        $entries = StadtraetInnenListEntry::parseHtmlList($html);

        foreach ($entries as $entry) {
            $this->parse($entry->id);
        }
    }

    public function parseUpdate(): void
    {
        $this->parseAll();
    }

    public function parseQuickUpdate(): void
    {

    }
}
