<?php

use SGH\PdfBox\PdfBox;

class RISPDF2Text
{

    public static $RIS_OCR_CLEAN_I_REPLACES = [
        "ı"           => "i",
        "vv"          => "w",
        "Munchen"     => "München",
        "Offentliche" => "Öffentliche",
        " fur "       => " für ",
        "reterat"     => "refereat",
        "A/t"         => "alt",
        "l\/l"        => "M",
        "\/\/"        => "W",
        "\/Wrt"       => "Wirt",
        "\/"          => "v",
        "i<"          => "k",
        "vorIage"     => "vorlage",
        "ßeamt"       => "Beamt",
        "u ße"        => "uße",
        "e ße"        => "eße",
        " dle "       => " die ",
        " lsar"       => " Isar",
        "Lander"      => "Länder",
        "Anderung"    => "Änderung",
        "vWWv"        => "www",
        "Vlﬁ"         => "Wi",
        "Scn"         => "sch",
    ];

    public static $RIS_OCR_CLEAN_REPLACES = [
        "nıv"     => "rw",
        " lsar"   => " Isar",
        "-lsar"   => "-Isar",
        "RlN"     => "RIN",
        "gen/ers" => "gervers",
        "Sen/er"  => "Server",
        "chen/er" => "cherver",
        "äahr"    => "Jahr",
        " ln"     => " In",
        " lm"     => " Im",
        " iange"  => " lange",
        "Grnbi-i" => "GmbH",
        "SWMIMVG" => "SWM/MVG",
        "MVGISWM" => "MVG/SWM",
        "tiich"   => "tlich",
        "Schuie"  => "Schule",
    ];

    public static $RIS_OCR_CLEAN_PREG_REPLACES = [
        "/^l([bcdfghkmnpqrstvwxz])/um" => "I\\1",
        "/ l([bcdfghkmnpqrstvwxz])/um" => " I\\1",
        "/ i([aeou])/um"               => " l\\1",
        "/(niv)(?!eau)/u"              => "rw",
    ];

    /**
     * @param string $filename
     * @return array
     */
    public static function document_pdf_metadata($filename)
    {
        $result = [];
        exec(PATH_PDFINFO . " '" . addslashes($filename) . "'", $result);
        $seiten = 0;
        $datum  = "";

        if (preg_match("/Pages:\\s*([0-9]+)/siu", implode("\n", $result), $matches_page)) $seiten = IntVal($matches_page[1]);
        if (preg_match("/CreationDate:\\s*([a-z0-9 :]+)\n/siu", implode("\n", $result), $matches_date)) {
            $datum = date_parse($matches_date[1]);
            if ($datum && isset($datum["year"]) && $datum["year"] > 1990) {
                $datum = $datum["year"] . "-" . $datum["month"] . "-" . $datum["day"] . " " . $datum["hour"] . ":" . $datum["minute"] . ":" . $datum["second"];
            } else {
                $datum = "0000-00-00 00:00:00";
            }
        }

        if ($seiten > 0) return ["seiten" => $seiten, "datum" => $datum];

        $result = [];
        exec(PATH_IDENTIFY . " $filename", $result);
        $anzahl = 0;
        foreach ($result as $res) if (strpos($res, "DirectClass")) $anzahl++;

        return ["seiten" => $anzahl, "datum" => $datum];
    }

    /**
     * @param string $filename
     * @param int $seiten_anzahl
     * @return string
     */
    public static function document_text_ocr($filename, $seiten_anzahl)
    {
        // Hint: PDF-parsing needs to be enabled in /etc/ImageMagick-6/policy.xml

        $depth = "-depth 8";
        $text  = "";

        if (preg_match("/tiff?$/siu", $filename)) { // TIFF
            $png_tmp_file = TMP_PATH . "ocr-tmp." . rand(0, 1000000000) . ".png";
            exec(PATH_TESSERACT . " $filename $png_tmp_file -l deu --oem 1", $result);
            if (file_exists($png_tmp_file . ".txt")) {
                $text .= file_get_contents($png_tmp_file . ".txt");
                unlink($png_tmp_file . ".txt");
            };
        } else for ($i = 0; $i < $seiten_anzahl; $i++) { // PDF
            $png_tmp_file = TMP_PATH . "ocr-tmp." . rand(0, 1000000000) . ".png";
            $exec         = PATH_CONVERT . " -background white -flatten ";
            $exec .= "-density 600 -resize 50% "; // => better font rendering quality
            $exec .= "\"${filename}[$i]\" -colorspace Gray $depth $png_tmp_file";
            exec($exec, $result);
            if (file_exists($png_tmp_file)) {
                exec(PATH_TESSERACT . " $png_tmp_file $png_tmp_file -l deu --oem 1", $result);
                $text .= "########## SEITE " . ($i + 1) . " ##########\n";
                if (file_exists($png_tmp_file . ".txt")) {
                    $text .= file_get_contents($png_tmp_file . ".txt");
                    unlink($png_tmp_file . ".txt");
                };
                unlink($png_tmp_file);
            }
        }
        return $text;
    }

    public static function document_text_pdf($pdf)
    {
        $converter = new PdfBox;
        $converter->setPathToPdfBox(PATH_PDFBOX);
        return $converter->textFromPdfFile($pdf);
    }

    public static function ris_ocr_clean($txt)
    {
        $ord_a = ord("a");
        $ord_z = ord("z");

        $txt = str_replace(" \n", "\n", $txt);
        $txt = str_replace("-\n", "", $txt);
        foreach (static::$RIS_OCR_CLEAN_I_REPLACES as $from => $to) $txt = str_ireplace($from, $to, $txt);
        foreach (static::$RIS_OCR_CLEAN_REPLACES as $from => $to) $txt = str_replace($from, $to, $txt);
        foreach (static::$RIS_OCR_CLEAN_PREG_REPLACES as $from => $to) $txt = preg_replace($from, $to, $txt);

        $first = -1;
        while ($first = strpos($txt, "I", $first + 1)) {
            if ($first == 0) continue;
            $c = ord($txt[$first - 1]);
            if ($c >= $ord_a && $c <= $ord_z) $txt[$first] = "l";
        }

        if (substr($txt, 0, 3) == "ll.") {
            $txt[0] = "I";
            $txt[1] = "I";
        }
        if (substr($txt, 0, 4) == "lll.") {
            $txt[0] = "I";
            $txt[1] = "I";
            $txt[2] = "I";
        }
        return $txt;
    }
}
