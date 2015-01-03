<?php


class StadtrechtParser extends RISParser
{
    public function parseIndex() {
        $all_docs = [];
        $index    = ris_download_string("http://www.muenchen.info/dir/recht/alph_portal.html");
        $lines    = explode("\n", $index);
        foreach ($lines as $line) {
            if(preg_match("/<td\><a href=\"(\S+)\.htm\" target=\"_blank\">([\S ]+)<\/a><\/td>/i", $line, $matches)) {
                $url_base = "http://www.muenchen.info/dir/recht/" . $matches[1];
                $titel    = $matches[2];
                $id       = preg_replace("/\S+\/(\S+)/", "$1", $matches[1]);
                array_push($all_docs, array($url_base, trim($titel), $id));
            }
        }
        return $all_docs;
    }

    public function parse($id) {
        // @TODO
    }

    // http://www.muenchen.info/dir/recht/23/23_20100525/css/23_20100525
    public function parseByURL($url_base, $titel, $id) {
        echo "Lese ein: " . $titel . "\n";

        $index  = ris_download_string($url_base . "/css/" . $id . "_index.htm");

        preg_match("/gLastPage = (?<seiten>[0-9]+);/siu", $index, $matches);
        $seiten = $matches["seiten"];
        if (!$seiten || $seiten < 1) throw new Exception("Konnte Seitenzahl nicht auslesen");

        $texte = "";
        $css = "";

        for ($seite = 1; $seite <= $seiten; $seite++) {
            $document = ris_download_string($url_base . "/css/" . $id . "_" . $seite . ".htm");

            // workaround for https://bugs.php.net/bug.php?id=61484
            ini_set('mbstring.substitute_character', "none");
            $document= mb_convert_encoding($document, 'UTF-8', 'UTF-8');

            $x = explode('<!-- text starts here -->', $document);
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

        $titel = html_entity_decode($titel, ENT_COMPAT, "UTF-8");;

        /** @var Rechtsdokument $rechtsdokument */
        if ($id > 0) $rechtsdokument = Rechtsdokument::model()->findByAttributes(array("id" => $id));
        else $rechtsdokument = Rechtsdokument::model()->findByAttributes(array("titel" => $titel));
        if (!$rechtsdokument) $rechtsdokument = new Rechtsdokument();

        $rechtsdokument->url_base = $url_base;
        $rechtsdokument->url_html = $url_base . "/css/" . $id . ".htm";
        $rechtsdokument->url_pdf  = $url_base . ".pdf";
        $rechtsdokument->id       = ($id > 0 ? $id : rand(100000, 999999));
        $rechtsdokument->titel    = $titel;
        $rechtsdokument->html     = $texte;
        $rechtsdokument->css      = $css;

        $rechtsdokument->save();
    }

    public function parseSeite($seite, $first) {

    }

    public function parseAlle() {
        $all_docs = $this->parseIndex();
        echo $all_docs[0][0] . ";" . $all_docs[0][1] . ";" . $all_docs[0][2]. "\n";
        foreach ($all_docs as $doc) {
            $this->parseByURL($doc[0], $doc[1], $doc[2]);
        }
    }

    public function parseUpdate() {

    }

}
