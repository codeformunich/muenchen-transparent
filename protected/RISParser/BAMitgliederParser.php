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

        GremienmitgliedschaftData::setGremienmitgliedschaftenToPerson($strIn, $parsed->fraktionsMitgliedschaften, Gremium::TYPE_BA_FRAKTION, $parsed->baNr);
        GremienmitgliedschaftData::setGremienmitgliedschaftenToPerson($strIn, $parsed->baMitgliedschaften, Gremium::TYPE_BA, $parsed->baNr);
        GremienmitgliedschaftData::setGremienmitgliedschaftenToPerson($strIn, $parsed->baAusschuesse, Gremium::TYPE_BA_UNTERAUSSCHUSS, $parsed->baNr);

        return $strIn;
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
