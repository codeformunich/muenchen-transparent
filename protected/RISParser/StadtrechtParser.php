<?php

class StadtrechtParser extends RISParser
{
    public function parseIndex() {
        $all_docs = [];
        $index    = ris_download_string("http://www.muenchen.info/dir/recht/alph_portal.html");
        $lines    = explode("\n", $index);
        foreach ($lines as $line) {
            if(preg_match("/<td\><a href=\"(\S+)\.htm\" target=\"_blank\">([\S ]+)<\/a><\/td>/i", $line, $matches)) {
                $kuerzel   = $matches[2];
                $name      = $matches[2];
                $base_url  = "http://www.muenchen.info/dir/recht/" . $matches[1];
                $id        = preg_replace("/\S+\/(\S+)/", "$1", $matches[1]);
                array_push($all_docs, array($base_url, $id, $kuerzel, $name));
            }
        }
        return $all_docs;
    }

    public function parse($id) {
        // @TODO
    }

    public function parseByURL($base_url, $id, $kuerzel, $name) {
        $full_url = $base_url . "/css/" . $id . "_index.htm";
        $index  = ris_download_string($full_url);

        preg_match("/gLastPage = (?<seiten>[0-9]+);/siu", $index, $matches);
        $seiten = $matches["seiten"];
        if (!$seiten || $seiten < 1) throw new Exception("Konnte Seitenzahl nicht auslesen");

        $texte = "";
        $css = "";

        for ($seite = 1; $seite <= $seiten; $seite++) {

            $document = ris_download_string($base_url . "/css/" . $id . "_" . $seite . ".htm");

            // workaround for https://bugs.php.net/bug.php?id=61484
            ini_set('mbstring.substitute_character', "none");
            $document= mb_convert_encoding($document, 'UTF-8', 'UTF-8');

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
        $rechtsdokument = Rechtsdokument::model()->findByAttributes(array("url_base" => $base_url . $id));
        if (!$rechtsdokument) $rechtsdokument = new Rechtsdokument();

        $rechtsdokument->name     = $name;
        $rechtsdokument->url_base = $full_url;
        $rechtsdokument->url_pdf  = $base_url . ".pdf";
        $rechtsdokument->html     = $texte;
        $rechtsdokument->css      = $css;
        $rechtsdokument->nr       = $kuerzel;

        $rechtsdokument->save();
        var_dump($rechtsdokument->getErrors());
    }

    public function parseSeite($seite, $first) {

    }

    public function parseAlle() {
        $all_docs = $this->parseIndex();
        echo $all_docs[0][0] . " " . $all_docs[0][1] . " " . $all_docs[0][2] . " " . $all_docs[0][3];
        foreach ($all_docs as $doc) {
            $this->parseByURL($doc[0], $doc[1], $doc[2], $doc[3]);
        }
    }

    public function parseUpdate() {
        // TODO
        $all_docs = $this->parseIndex();
        foreach ($all_docs as $doc) {
            parseByURL($doc[0], $doc[1], $doc[2], $doc[3]);
        }
    }

}
