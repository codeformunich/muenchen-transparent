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

        try {
            $downloader = new BrowserBasedDowloader();
            $downloader->page->navigate('https://risi.muenchen.de/risi/erweitertesuche')->waitForNavigation();


            $downloader->page->evaluate('$("#id3").val("0").trigger("change");');
            $downloader->waitForElementToAppear('#id5');

            $downloader->page->evaluate('document.querySelector("#id5").value = "2020-07-01"');
            $downloader->page->evaluate('document.querySelector("#id6").value = "2020-08-01"');
            $downloader->clickJs('#idc button[type=submit]');
            $downloader->page->waitForReload();

            $html = '';

            $goon = true;
            for ($i = 0; $i < 100 && $goon; $i++) {
                echo "Iteration $i\n";
                $html .= $downloader->getInnerHtml('#id2 .list-group-flush');
                if ($downloader->seeElement('#id2 a[rel=next]')) {
                    $downloader->page->evaluate('document.querySelector("#id2 .list-group-flush").remove()')->waitForResponse();

                    $downloader->clickJs('#id2 a[rel=next]');

                    $downloader->waitForElementToAppear('#id2 .list-group-flush');
                } else {
                    $goon = false;
                }
            }


            echo $html;
        } finally {
            // bye
            $downloader->close();
        }
    }
}
