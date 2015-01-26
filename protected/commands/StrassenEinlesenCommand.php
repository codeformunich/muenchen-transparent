<?php

class StrassenEinlesenCommand extends CConsoleCommand
{

    public function run($args)
    {
        for ($i = ord('A'); $i <= ord('Z'); $i++) {
            $txt = RISTools::load_file('http://stadt-muenchen.net/strassen/index.php?name=' . chr($i));
            $txt = explode("<table class='full' border='0'>", $txt);
            $txt = explode("</table>", $txt[1]);

            preg_match_all("/<tr><td>(.*)<\/tr>/siuU", $txt[0], $matches);
            foreach ($matches[1] as $match) {
                $y            = explode('</a></td><td>', $match);
                $strassenname = preg_replace("/(s)traße$/siu", "\\1tr.", trim(strip_tags($y[0])));
                $plz          = trim(strip_tags($y[1]));

                $str = Strasse::model()->findByAttributes(array("name" => $strassenname));
                if (!$str) {
                    echo "Neu: " . $plz . " - " . $strassenname . "\n";
                    $str          = new Strasse();
                    $str->name    = $strassenname;
                    $str->plz     = $plz;
                    $str->osm_ref = 0;
                    if (!$str->save()) {
                        var_dump($str->getErrors());
                    }

                }
            }
        }
    }


}