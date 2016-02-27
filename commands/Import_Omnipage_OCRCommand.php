<?php

class Import_Omnipage_OCRCommand extends CConsoleCommand
{
    private function importFile($filename)
    {
        echo $filename . "\n";
        $txt = file_get_contents(OMNIPAGE_DST_DIR . $filename);
        //$txt = iconv("Windows-1252", "UTF-8//TRANSLIT", $txt);
        $txt = iconv('UTF-16LE', "UTF-8//TRANSLIT", $txt);
        /*
        $repl = array(
            chr(194) . chr(149) => " ",
            chr(194) . chr(151) => " ",

            chr(194) . chr(148) => "„",
            chr(194) . chr(150) => "„",
            chr(194) . chr(132) => "„",
            chr(194) . chr(130) => "„",

            chr(194) . chr(147) => "“",
        );
        //$txt = str_replace(array_keys($repl), array_values($repl), $txt);
        echo $txt;
        if (strpos($txt, "Tagessatz in ") !== false) {
            $x = explode("Tagessatz in ", $txt);
            echo ord($x[1][0]) . " - " . ord($x[1][1]) . " - " . ord($x[1][2]);
        }
        */

        /** @var Dokument $dokument */
        $dokument = Dokument::find()->findByPk(IntVal($filename));
        if (!$dokument)  {
            rename(OMNIPAGE_DST_DIR . $filename, OMNIPAGE_IMPORTED_DIR . $filename);
            if (file_exists(OMNIPAGE_PDF_DIR . IntVal($filename) . ".pdf")) unlink(OMNIPAGE_PDF_DIR . IntVal($filename) . ".pdf");
            return;
        }

        $dokument->text_ocr_raw            = $txt;
        $dokument->text_ocr_corrected      = $txt;
        $dokument->text_ocr_garbage_seiten = NULL;
        $dokument->ocr_von                 = Dokument::$OCR_VON_OMNIPAGE;
        $dokument->save();

        $dokument->geo_extract();
        $dokument->solrIndex();

        rename(OMNIPAGE_DST_DIR . $filename, OMNIPAGE_IMPORTED_DIR . $filename);
        if (file_exists(OMNIPAGE_PDF_DIR . IntVal($filename) . ".pdf")) unlink(OMNIPAGE_PDF_DIR . IntVal($filename) . ".pdf");
    }

    public function run($args)
    {
        if (count($args) > 0) $this->importFile($args[0] . ".txt");
        else {
            $dh = opendir(OMNIPAGE_DST_DIR);
            while (($file = readdir($dh)) !== false) if (is_file(OMNIPAGE_DST_DIR . $file)) {
                if (strpos($file, ".txt") !== false) $this->importFile($file);
            }
            closedir($dh);
        }
    }
}
