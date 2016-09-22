<?php

class Reindex_DocumentsCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (count($args) == 0) die("./yiic reindex_documents [max_id]\n");
        $max_id = $args[0];

        $sql = Yii::app()->db->createCommand();
        $sql->select("id")->from("dokumente")->where("id >= " . IntVal($max_id))->order("id DESC");
        $data = $sql->queryColumn(["id"]);

        $anz = count($data);
        foreach ($data as $nr => $dok_id) {
            echo "$nr von $anz: $dok_id\n";
            /** @var Dokument $dokument */
            $dokument = Dokument::model()->findByPk($dok_id);
            if ($dokument) {
                //$dokument->geo_extract();
                //$dokument->solrIndex();
                $dokument->reDownloadIndex();
            }
        }
    }
}
