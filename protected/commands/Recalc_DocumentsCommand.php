<?php

class Recalc_DocumentsCommand extends CConsoleCommand
{
    public function run($args)
    {

        define("VERYFAST", true);

        if (count($args) == 0) die("./yii recalc_documents [Dokument-ID|alle]\n");

        if ($args[0] == "alle") {
            $sql = Yii::app()->db->createCommand();
            $sql->select("id")->from("dokumente")->where("id >= 579866")->order("id");
            $data = $sql->queryColumn(array("id"));
        } else {
            $data = array(IntVal($args[0]));
        }

        $anz = count($data);
        foreach ($data as $nr => $dok_id) {
            echo "$nr / $anz => $dok_id\n";
            /** @var Dokument $dokument */
            $dokument = Dokument::model()->findByPk($dok_id);
            if (!$dokument) continue;

            $dokument->download_if_necessary();
            $dokument->geo_extract();

            $absolute_filename = $dokument->getLocalPath();
            $metadata                 = RISPDF2Text::document_pdf_metadata($absolute_filename);
            $dokument->seiten_anzahl  = $metadata["seiten"];
            $dokument->datum_dokument = $metadata["datum"];
            $dokument->save();

            echo $dokument->id . " => " . $dokument->seiten_anzahl . " / " . $dokument->datum_dokument . "\n";
        }
    }
}