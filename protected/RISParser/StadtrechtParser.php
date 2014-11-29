<?php


class StadtrechtParser extends RISParser
{


    public function parse($id) {
        // @TODO
    }

    // http://www.muenchen.info/dir/recht/23/23_20100525/css/23_20100525
    public function parseByURL($url, $url_pdf, $kuerzel, $name) {
        echo $url . "_index.htm\n";
        $index  = ris_download_string($url . "_index.htm");

        preg_match("/gLastPage = (?<seiten>[0-9]+);/siu", $index, $matches);
        $seiten = $matches["seiten"];
        if (!$seiten || $seiten < 1) throw new Exception("Konnte Seitenzahl nicht auslesen");

        $texte = "";
        $css = "";

        for ($seite = 1; $seite <= $seiten; $seite++) {

            $document = ris_download_string($url . "_" . $seite . ".htm");

            $document = iconv("UTF8", "UTF8//IGNORE", $document);

            $x = explode('!-- text starts here -->', $document);
            $x = explode('</BODY>', $x[1]);
            $text = $x[0];

            $html = str_replace(array("<NOBR>", "</NOBR>", "<SPAN", "</SPAN"), array("", "", "<DIV", "</DIV"), $text);


            preg_match("/text positioning information \*\/\\n(?<css>.*)<\/STYLE/siu", $document, $matches);
            $x = explode('* text positioning information */', $document);
            $x = explode('/* bitmap image information */', $x[1]);

            $css_src = $x[0];

            $x = explode("\n", $css_src);
            foreach ($x as $y) {
                if (substr($y, 0, 3) != ".ps" && substr($y, 0, 3) != ".ft") continue;
                $css .= ".seite" . $seite . " " . $y . "\n";
            }

            $texte .= '<section class="seite seite' . $seite . '">' . $html . '</section>' . "\n\n\n";

        }

        echo $css;
        /** @var Rechtsdokument $rechtsdokument */
        $rechtsdokument = Rechtsdokument::model()->findByAttributes(array("url_base" => $url));
        if (!$rechtsdokument) $rechtsdokument = new Rechtsdokument();

        $rechtsdokument->name = $name;
        $rechtsdokument->url_base = $url;
        $rechtsdokument->url_pdf = $url_pdf;
        $rechtsdokument->html = $texte;
        $rechtsdokument->css = $css;
        $rechtsdokument->nr = $kuerzel;

        $rechtsdokument->save();
        var_dump($rechtsdokument->getErrors());
    }

    public function parseSeite($seite, $first) {

    }

    public function parseAlle() {

    }

    public function parseUpdate() {

    }

}