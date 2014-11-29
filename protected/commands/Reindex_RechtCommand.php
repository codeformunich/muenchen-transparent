<?php

//define("VERYFAST", true);

class Reindex_RechtCommand extends CConsoleCommand {
    public function run($args) {

        $parser = new StadtrechtParser();
        $parser->parseByURL("http://www.muenchen.info/dir/recht/1/1_20131017/css/1_20131017", "http://www.muenchen.info/dir/recht/1/1_20131017.pdf", "HauptS", "Hauptsatzung");

    }
}