<?php

class Reindex_DocumentCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (!isset($args[0]) || $args[0] <= 1) die("./yiic reindex_document [dokument-ID]\n");

        /** @var Dokument $dokument */
        $dokument = Dokument::model()->findByPk(intval($args[0]));
        if (!$dokument) {
            echo "Document not found\n";
        }

        $dokument->reDownloadIndex();
    }
}
