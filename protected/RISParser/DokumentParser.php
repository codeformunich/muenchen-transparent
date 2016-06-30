<?php

class DokumentParser
{

    public function checkAndDeleteDocument($doc_id)
    {
        /** @var Dokument $dokument */
        $dokument = Dokument::model()->disableDefaultScope()->findByPk($doc_id);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $dokument->getLinkZumOrginal());
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD'); // here HTTP request is 'HEAD'

        curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($info["http_code"] == 200 && $dokument->deleted == 1) {
            // @TODO Wiederherstellen
            echo "\n";
        } elseif ($info["http_code"] == 404 && $dokument->deleted == 0) {
            $dokument->loeschen();
            echo "Gelöscht: " . $dokument->id . "\n";
        }
    }
}
