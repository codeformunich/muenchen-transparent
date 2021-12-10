<?php

//define("VERYFAST", true);

use HeadlessChromium\BrowserFactory;

class DownloadTestCommand extends CConsoleCommand
{


    public function run($args)
    {
        //echo RISDownloader::downloadSessionCookie();
        //$resp = RISDownloader::downloadStrAntragIndex();
        //echo $resp;


        $parser = new StadtratsantragParser();
        $parser->parseMonth(2021, 10);
    }
}
