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
            $strIn->id           = $parsed->id;
            $strIn->referentIn   = 0;
            $strIn->bio          = "";
            $strIn->web          = "";
            $strIn->beruf        = "";
            $strIn->beschreibung = "";
            $strIn->quellen      = "";
            $strIn->gewaehlt_am  = null;
        }
        $strIn->name         = $parsed->name;
        $strIn->save();

        if (isset($parsed->baNr)) {
            $baNr = $parsed->baNr;
        } else {
            $baNr = null;
            foreach ($strIn->mitgliedschaften as $mitgliedschaft) {
                if ($mitgliedschaft->gremium->ba_nr > 0) {
                    $baNr = $mitgliedschaft->gremium->ba_nr;
                }
            }
            echo "Warnung: BA-Mitglied ohne erkannte BA-Zuordnung: " . $strIn->name . "\n";
        }

        GremienmitgliedschaftData::setGremienmitgliedschaftenToPerson($strIn, $parsed->fraktionsMitgliedschaften, Gremium::TYPE_BA_FRAKTION, $baNr);
        GremienmitgliedschaftData::setGremienmitgliedschaftenToPerson($strIn, $parsed->baMitgliedschaften, Gremium::TYPE_BA, $baNr);
        GremienmitgliedschaftData::setGremienmitgliedschaftenToPerson($strIn, $parsed->baAusschuesse, Gremium::TYPE_BA_UNTERAUSSCHUSS, $baNr);

        return $strIn;
    }

    public function parseAll(): void
    {
        $html = $this->browserBasedDowloader->downloadPersonList(BrowserBasedDowloader::PERSON_TYPE_BA_MITGLIEDER);

        $entries = StadtraetInnenListEntry::parseHtmlList($html);

        $ids = [];
        foreach ($entries as $entry) {
            $mitglied = $this->parse($entry->id);
            $ids[] = $mitglied->id;
        }

        // parse members that are not in the current list anymore, but have been in the past and therefore still have an DB entry
        /** @var CDbCommand $sql */
        $sql = Yii::app()->db->createCommand('
            SELECT DISTINCT(str.id) FROM stadtraetInnen str JOIN stadtraetInnen_gremien strgr ON str.id = strgr.stadtraetIn_id
                JOIN gremien gr ON strgr.gremium_id = gr.id
                WHERE gr.ba_nr > 0 AND str.id NOT IN (' . implode(', ', $ids) . ')'
        );
        foreach ($sql->query() as $row) {
            $this->parse(intval($row['id']));
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
