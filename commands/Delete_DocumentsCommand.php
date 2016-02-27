<?php

use Yii;

class Delete_DocumentsCommand extends ConsoleCommand
{
    public function run($args)
    {
        if (count($args) == 0) die("./yii delete_documents [Dokument-ID|alle]\n");

        $parser = new DokumentParser();

        if ($args[0] > 0) {
            $parser->checkAndDeleteDocument($args[0]);
        }
        if ($args[0] == "alle") {
            $sql = Yii::$app->db->createCommand();
            $sql->select("id")->from("dokumente")->order("id DESC");
            $data = $sql->queryColumn(["id"]);

            for ($i = 0; $i < count($data); $i++) {
                if (($i % 100) == 0) echo $i . " / " . count($data) . "\n";
                $parser->checkAndDeleteDocument($data[$i]);
            }
        }
        echo "\n";
    }
}
