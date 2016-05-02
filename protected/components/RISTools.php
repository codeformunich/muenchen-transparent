<?php

use Zend\Mail\Message;

class RISTools
{

    const STD_USER_AGENT = "RISParser (Muenchen Transparent)";

    /**
     * @param string $text
     * @return string
     */
    public static function toutf8($text)
    {
        if (!function_exists('mb_detect_encoding')) {
            return $text;
        } elseif (mb_detect_encoding($text, 'UTF-8, ISO-8859-1') == "ISO-8859-1") {
            return utf8_encode($text);
        } else {
            return $text;
        }
    }

    /**
     * @param $string
     * @return string
     */
    public static function bracketEscape($string)
    {
        return str_replace(["[", "]"], [urlencode("["), urlencode("]")], $string);
    }

    /**
     * @param string $url_to_read
     * @param string $username
     * @param string $password
     * @param int $timeout
     * @return string
     */
    public static function load_file($url_to_read, $username = "", $password = "", $timeout = 30)
    {
        $i = 0;
        do {
            $ch = curl_init();

            if ($username != "" || $password != "") {
                curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            }

            curl_setopt($ch, CURLOPT_URL, $url_to_read);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            $text = curl_exec($ch);
            $text = str_replace(chr(13), "\n", $text);
            //$info = curl_getinfo($ch);
            curl_close($ch);

            $text = RISTools::toutf8($text);

            if (!defined("VERYFAST")) {
                sleep(1);
            }
            $i++;
        } while (strpos($text, "localhost:8118") !== false && $i < 10);

        return $text;
    }

    /**
     * @param string $url_to_read
     * @param string $filename
     * @param string $username
     * @param string $password
     * @param int $timeout
     */
    public static function download_file($url_to_read, $filename, $username = "", $password = "", $timeout = 30)
    {
        $ch = curl_init();

        if ($username != "" || $password != "") {
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_to_read);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD'); // here HTTP request is 'HEAD'

        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info["http_code"] != 200 && $info["http_code"] != 403) {
            echo "Not found: $url_to_read\n";
            return;
        }

        $fp  = fopen($filename, "w");
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $url_to_read);
        curl_setopt($ch2, CURLOPT_HEADER, 0);
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch2, CURLOPT_USERAGENT, RISTools::STD_USER_AGENT);
        //curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_FILE, $fp);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
        curl_exec($ch2);
        $info = curl_getinfo($ch2);
        curl_close($ch2);
        //file_put_contents($filename, $text);
        fclose($fp);

        if (!defined("VERYFAST")) {
            sleep(1);
        }
    }


    /**
     * @param string $input
     * @return int
     */
    public static function date_iso2timestamp($input)
    {
        $x    = explode(" ", $input);
        $date = explode("-", $x[0]);

        if (count($x) == 2) {
            $time = explode(":", $x[1]);
        } else {
            $time = [0, 0, 0];
        }

        return mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
    }


    /**
     * @param string $input
     * @return string
     */
    public static function datumstring($input)
    {
        $ts  = static::date_iso2timestamp($input);
        $tag = date("d.m.Y", $ts);
        if ($tag == date("d.m.Y")) {
            return "Heute";
        }
        if ($tag == date("d.m.Y", time() - 3600 * 24)) {
            return "Gestern";
        }
        if ($tag == date("d.m.Y", time() - 2 * 3600 * 24)) {
            return "Vorgestern";
        }
        return $tag;
    }


    /**
     * @param string $text
     * @return string
     */
    public static function rssent($text)
    {
        $search  = ["<br>", "&", "\"", "<", ">", "'", "–"];
        $replace = ["\n", "&amp;", "&quot;", "&lt;", "&gt;", "&apos;", "-"];
        return str_replace($search, $replace, $text);
    }

    /**
     * @param string $titel
     * @return string
     */
    public static function korrigiereTitelZeichen($titel)
    {
        $titel = trim($titel);
        $titel = str_replace(chr(194) . chr(160), " ", $titel);
        $titel = preg_replace("/([\\s-\(])\?(\\w[^\\?]*[\\w\.\!])\?/siu", "\\1„\\2“", $titel);
        $titel = preg_replace("/([\\s-\(])\"(\\w[^\\?]*[\\w\.\!])\"/siu", "\\1„\\2“", $titel);
        $titel = str_replace(" ?", " —", $titel);
        $titel = preg_replace("/^\?(\\w[^\\?]*[\\w\.\!])\?/siu", "„\\1“", $titel);
        $titel = preg_replace("/([0-9])\?([0-9])/siu", " \\1-\\2", $titel);
        $titel = preg_replace("/\\s\?$/siu", "?", $titel);
        $titel = str_replace(chr(10) . "?", " —", $titel);
        $titel = str_replace("Â?", "€", $titel);
        return $titel;
    }


    /**
     * @param string $titel
     * @return string
     */
    public static function korrigiereDokumentenTitel($titel)
    {
        $titel = trim(str_replace("_", " ", $titel));

        if (preg_match("/^[0-9]+to[0-9]+$/siu", $titel)) {
            return "Tagesordnung";
        } // 25to13012015
        if (preg_match("/^to ba[0-9]+ [0-9\.]+(\-ris)?$/siu", $titel)) {
            return "Tagesordnung";
        } // z.B. https://www.ris-muenchen.de/RII/BA-RII/ba_sitzungen_dokumente.jsp?Id=3218578
        if (preg_match("/^to [0-9\. ]+$/siu", $titel)) {
            return "Tagesordnung";
        } // 2014 01 to
        if (preg_match("/^[0-9\. ]+ to$/siu", $titel)) {
            return "Tagesordnung";
        } // to 150108
        if (preg_match("/^(?<name>Einladung.*) [0-9\.]+( \(oeff\))?$/siu", $titel, $matches)) {
            return $matches["name"];
        } // Einladung UA BSB 10.12.2014 (oeff)
        if (preg_match("/^(?<name>Nachtrag.*) [0-9\.]+( \(oeff\))?$/siu", $titel, $matches)) {
            return $matches["name"];
        } // Einladung UA BSB 10.12.2014 (oeff)
        if (preg_match("/^[0-9]+(sondersitzung )?prot[0-9]+(oeff)?$/siu", $titel)) {
            return "Protokoll";
        }  // 25prot13012015, 23sondersitzung prot1114öff,  23prot1114öff
        if (preg_match("/^[0-9]+n?v?to[0-9]+oeff$/siu", $titel)) {
            return "Tagesordnung";
        }  // 21vto0115oeff
        if (preg_match("/^pro ba[0-9]+ [0-9\.]+(\-ris)?$/siu", $titel)) {
            return "Protokoll";
        } // z.B. https://www.ris-muenchen.de/RII/BA-RII/ba_sitzungen_dokumente.jsp?Id=3218508
        if (preg_match("/^prot?[0-9]+( ?oeff)?$/siu", $titel)) {
            return "Protokoll";
        } // pro140918 oeff
        if (preg_match("/^Einladung [0-9-]+$/siu", $titel)) {
            return "Einladung";
        } // Einladung 02-15

        $titel = preg_replace("/^( vom)? \\d\\d\.\\d\\d\.\\d{4}$/siu", "", $titel);
        $titel = preg_replace("/ \(oeff\)$/", "", $titel);

        if ($titel == "to") {
            return "Tagesordnung";
        }
        if ($titel == "Einladung oeffentlich") {
            return "Einladung";
        }
        if (preg_match("/^to [0-9\-]+ nachtrag/siu", $titel)) {
            return "Nachtrag";
        }

        $titel = preg_replace("/^V [0-9]+ /", "", $titel);
        $titel = preg_replace("/^(VV|VPA|KVA) ?[0-9 \.\-]+ (TOP)?/", "", $titel);
        $titel = preg_replace("/^OE V[0-9]+ /", "", $titel);
        $titel = preg_replace("/^[0-9]{2}\-[0-9]{2}\-[0-9]{2} +/", "", $titel);
        $titel = preg_replace("/ vom [0-9]{2}\.[0-9]{2}\.[0-9]{4}/", "", $titel);
        $titel = preg_replace("/[-_ ]?ris/i", "", $titel);
        $titel = preg_replace("/^(CSU|SPD|B90GrueneRL|OeDP|DIE LINKE|AfD) \-? ?Antrag/siU", "Antrag", $titel);

        $titel = preg_replace_callback("/(?<jahr>20[0-9]{2})(?<monat>[0-1][0-9])(?<tag>[0-9]{2})/siu", function ($matches) {
            return $matches['tag'] . '.' . $matches['monat'] . '.' . $matches['jahr'];
        }, $titel);
        $titel = preg_replace_callback("/(?<jahr>20[0-9]{2})\-(?<monat>[0-1][0-9])\-(?<tag>[0-9]{2})/siu", function ($matches) {
            return $matches['tag'] . '.' . $matches['monat'] . '.' . $matches['jahr'];
        }, $titel);
        $titel = preg_replace_callback("/(?<tag>[0-9]{2})(?<monat>[0-1][0-9])(?<jahr>20[0-9]{2})/siu", function ($matches) {
            return $matches['tag'] . '.' . $matches['monat'] . '.' . $matches['jahr'];
        }, $titel);

        // Der Name der Anfrage/des Antrags steht schon im Titel des Antrags => Redundant
        if (preg_match("/^Antrag[ \.]/", $titel)) {
            $titel = "Antrag";
        }
        if (preg_match("/^Anfrage[ \.]/", $titel)) {
            $titel = "Anfrage";
        }

        $titel = preg_replace_callback("/^(?<anfang>Anlage [0-9]+ )(?<name>.+)$/", function ($matches) {
            return $matches["anfang"] . " (" . trim($matches["name"]) . ")";
        }, $titel);

        $titel = str_replace(["Ae", "Oe", "Ue", "ae", "oe", "ue"], ["Ä", "Ö", "Ü", "ä", "ö", "ü"], $titel); // @TODO: False positives filtern? Geht das überhaupt?
        $titel = preg_replace("/(n)eü/siu", "$1eue", $titel);
        $titel = preg_replace("/aü/siu", "aue", $titel);

        if ($titel == "Deckblatt VV") {
            return "Deckblatt";
        }
        if ($titel == "Niederschrift (oeff)") {
            return "Niederschrift";
        }

        return trim($titel);
    }


    /**
     * @param string $str
     * @return array
     */
    public static function normalize_antragvon($str)
    {
        $a   = explode(",", $str);
        $ret = [];
        foreach ($a as $y) {
            $z = explode(";", $y);
            if (count($z) == 2) {
                $y = $z[1] . " " . $z[0];
            }
            $name_orig = $y;

            $y = mb_strtolower($y);
            $y = str_replace("herr ", "", $y);
            $y = str_replace("herrn ", "", $y);
            $y = str_replace("frau ", "", $y);
            $y = str_replace("str ", "", $y);
            $y = str_replace("str. ", "", $y);
            $y = str_replace("strin ", "", $y);
            $y = str_replace("berufsm. ", "", $y);
            $y = str_replace("dr. ", "", $y);
            $y = str_replace("prof. ", "", $y);

            $y = trim($y);

            if (mb_substr($y, 0, 3) == "ob ") {
                $y = mb_substr($y, 3);
            }
            if (mb_substr($y, 0, 3) == "bm ") {
                $y = mb_substr($y, 3);
            }

            for ($i = 0; $i < 10; $i++) {
                $y = str_replace("  ", " ", $y);
            }
            $y = str_replace("Zeilhofer-Rath", "Zeilnhofer-Rath", $y);

            if (trim($y) != "") {
                $ret[] = ["name" => $name_orig, "name_normalized" => $y];
            }
        }
        return $ret;
    }


    /**
     * @param string $name_normalized
     * @param string $name
     * @return Person
     */
    public function ris_get_person_by_name($name_normalized, $name)
    {
        /** @var Person $p */
        $p = Person::model()->findByAttributes(["name_normalized" => $name_normalized]);
        if ($p) {
            return $p;
        }
        echo "$name / $name_normalized \n";

        $p                  = new Person();
        $p->name_normalized = $name_normalized;
        $p->name            = $name;
        $p->typ             = "sonstiges";
        $p->save();
        return $p;
    }


    /**
     * @param string $typ
     * @param int $ba_nr
     * @return string
     */
    public static function ris_get_original_name($typ, $ba_nr)
    {
        switch ($typ) {
            case "ba_antrag":
                return "BA $ba_nr Antrag";
                break;
            case "ba_initiative":
                return "BA $ba_nr Initiative";
                break;
            case "ba_termin":
                return "BA $ba_nr Termin";
                break;
            case "stadtrat_antrag":
                return "Stadtratsantrag";
                break;
            case "stadtrat_vorlage":
                return "Stadtratsvorlage";
                break;
            case "stadtrat_termin":
                return "Stadtratssitzung";
                break;
        }
        return "Unbekannt";
    }

    /**
     * @param string $typ
     * @param int $ba_nr
     * @param int $id
     * @param string $mode
     * @return string
     */
    public static function ris_get_original_url($typ, $ba_nr, $id, $mode = "")
    {
        switch ($typ) {
            case "ba_antrag":
                return "https://www.ris-muenchen.de/RII/BA-RII/ba_antraege_details.jsp?Id=" . $id . "&selTyp=BA-Antrag";
                break;
            case "ba_initiative":
                return "https://www.ris-muenchen.de/RII/BA-RII/ba_initiativen_details.jsp?Id=" . $id;
                break;
            case "ba_termin":
                return "https://www.ris-muenchen.de/RII/BA-RII/ba_sitzungen_details.jsp?Id=" . $id;
                break;
            case "stadtrat_antrag":
                return "https://www.ris-muenchen.de/RII/RII/ris_antrag_detail.jsp?risid=" . $id;
                break;
            case "stadtrat_vorlage":
                return "https://www.ris-muenchen.de/RII/RII/ris_vorlagen_detail.jsp?risid=" . $id;
                break;
            case "stadtrat_termin":
                return "https://www.ris-muenchen.de/RII/RII/ris_sitzung_detail.jsp?risid=" . $id;
                break;
        }
        return "Unbekannt";
    }

    /**
     * @param string $email
     * @param string $betreff
     * @param string $text_plain
     * @param null|string $text_html
     * @param null|string $mail_tag
     */
    public static function send_email($email, $betreff, $text_plain, $text_html = null, $mail_tag = null)
    {
        if (defined("MAILGUN_API_KEY") && strlen(MAILGUN_API_KEY) > 0 && $mail_tag != "system") {
            $message = new \SlmMail\Mail\Message\Mailgun();
            $message->setOption('tracking', false);
            $client = new \Zend\Http\Client();
            $client->setAdapter(new \Zend\Http\Client\Adapter\Curl());
            $service = new \SlmMail\Service\MailgunService(MAILGUN_DOMAIN, MAILGUN_API_KEY);
            $service->setClient($client);
            $transport = new \SlmMail\Mail\Transport\HttpTransport($service);
        } else {
            $message   = new Zend\Mail\Message();
            $transport = new Zend\Mail\Transport\Sendmail();
        }
        static::set_zend_email_data($message, $email, $betreff, $text_plain, $text_html);

        $transport->send($message);
    }

    /**
     * @param Message $message
     * @param string $email
     * @param string $betreff
     * @param string $text_plain
     * @param string|null $text_html
     */
    private static function set_zend_email_data($message, $email, $betreff, $text_plain, $text_html = null)
    {
        $message->setFrom(Yii::app()->params["adminEmail"], Yii::app()->params["adminEmailName"]);
        $message->addTo($email, $email);
        $message->setSubject($betreff);

        $message->setEncoding("UTF-8");

        if ($text_html !== null) {
            $converter = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles($text_html);
            $converter->setStripOriginalStyleTags(false);
            $converter->setUseInlineStylesBlock(true);
            $converter->setEncoding("UTF-8");
            $converter->setExcludeMediaQueries(true);
            $converter->setCleanup(false);
            $text_html = $converter->convert();

            $text_part          = new Zend\Mime\Part($text_plain);
            $text_part->type    = "text/plain";
            $text_part->charset = "UTF-8";
            $html_part          = new Zend\Mime\Part($text_html);
            $html_part->type    = "text/html";
            $html_part->charset = "UTF-8";
            $mimem              = new Zend\Mime\Message();
            $mimem->setParts([$text_part, $html_part]);

            $message->setBody($mimem);
            $message->getHeaders()->get('content-type')->setType('multipart/alternative');
        } else {
            $message->setBody($text_plain);
            $headers = $message->getHeaders();
            $headers->removeHeader('Content-Type');
            $headers->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
        }
    }

    /**
     * @param string[] $arr
     * @return string[]
     */
    public static function makeArrValuesUnique($arr)
    {
        $val_count = [];
        foreach ($arr as $elem) {
            if (isset($val_count[$elem])) {
                $val_count[$elem]++;
            } else {
                $val_count[$elem] = 1;
            }
        }
        $vals_used = [];
        foreach ($arr as $i => $elem) {
            if ($val_count[$elem] == 1) {
                continue;
            }
            if (isset($vals_used[$elem])) {
                $vals_used[$elem]++;
            } else {
                $vals_used[$elem] = 1;
            }
            $arr[$i] = $elem . " (" . $vals_used[$elem] . ")";
        }
        return $arr;
    }

    /**
     * @param string $text_html
     * @return string
     */
    public static function insertTooltips($text_html)
    {
        /** @var Text[] $eintraege */
        $eintraege    = Text::model()->findAllByAttributes([
            "typ" => Text::$TYP_GLOSSAR,
        ]);
        $regexp_parts = [];
        /** @var Text[] $tooltip_replaces */
        $tooltip_replaces = [];
        foreach ($eintraege as $ein) {
            $aliases = [strtolower($ein->titel)];
            if ($ein->titel == "Fraktion") {
                $aliases[] = "fraktionen";
            }
            if ($ein->titel == "Ausschuss") {
                $aliases[] = "aussch&uuml;ssen";
            }

            foreach ($aliases as $alias) {
                $regexp_parts[]           = preg_quote($alias);
                $tooltip_replaces[$alias] = $ein;
            }
        }
        $text_html = preg_replace_callback("/(?<pre>[^\\w])(?<word>" . implode("|", $regexp_parts) . ")(?<post>[^\\w])/siu", function ($matches) use ($tooltip_replaces) {
            $eintrag = $tooltip_replaces[strtolower($matches["word"])];
            $text    = strip_tags(html_entity_decode($eintrag->text, ENT_COMPAT, "UTF-8"));
            if (strlen($text) > 200) {
                $text = substr($text, 0, 198) . "... [weiter]";
            }
            $link         = CHtml::encode(Yii::app()->createUrl("infos/glossar") . "#" . $eintrag->titel);
            $replace_html = '<a href="' . $link . '" class="tooltip_link" data-toggle="tooltip" data-placement="top" title="" data-original-title="' . CHtml::encode($text) . '">' . $matches["word"] . '</a>';
            return $matches["pre"] . $replace_html . $matches["post"];

        }, $text_html);
        /*
        foreach ($eintraege as $eintrag) if ($eintrag->titel == "Stadtrat") {

        }
        */
        return $text_html;
    }
}
