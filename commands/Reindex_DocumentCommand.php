<?php

use Yii;
use app\models\Dokument;

class Reindex_DocumentCommand extends ConsoleCommand
{
    public function run($args)
    {
        if (!isset($args[0]) || $args[0] <= 1) die("./yiic reindex_document [dokument-ID]\n");

        $sql = Yii::$app->db->createCommand();
        $sql->select("id")->from("dokumente")->where("id = " . IntVal($args[0]));
        $data = $sql->queryColumn(["id"]);

        $anz = count($data);
        foreach ($data as $nr => $dok_id) {
            echo "$nr / $anz => $dok_id\n";
            /** @var Dokument $dokument */
            $dokument = Dokument::findOne($dok_id);
            if (!$dokument) continue;

            //$dokument->reDownloadIndex();
            $dokument->geo_extract();
            $dokument->solrIndex();
        }
    }
}
