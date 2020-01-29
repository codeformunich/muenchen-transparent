<?php

class StrassenEinlesenCommand extends CConsoleCommand
{
    public function run($args)
    {
        for ($jahr = 2009; $jahr <= 2019; $jahr++) {
            if ($jahr >= 2014) {
                $url = 'https://www.muenchen.de/rathaus/Stadtverwaltung/Kommunalreferat/geodatenservice/strassennamen/' . $jahr . '.html';
            } else {
                $url = 'https://www.muenchen.de/rathaus/Stadtverwaltung/Kommunalreferat/geodatenservice/strassennamen/Strassenneubenennung-' . $jahr . '.html';
            }
            $txt = RISTools::load_file($url);

            $txt = explode("begin: lay-teaser", $txt);
            $txt = explode("end: mod-list", $txt[1]);

            preg_match_all("/<a[^<]+_self\">(?<strasse>[^<]*) <svg/siu", $txt[0], $matches);
            foreach ($matches['strasse'] as $strasse) {
                $strassenname = preg_replace("/(s)tra(ß|ss)e$/siu", "\\1tr.", trim(strip_tags($strasse)));
                $plz = '';

                $str = Strasse::model()->findByAttributes(["name" => $strassenname]);
                if (!$str) {
                    echo "Neu: " . $strassenname . "\n";
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
