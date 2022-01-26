<?php

class StrassenEinlesenCommand extends CConsoleCommand
{
    public function run($args)
    {
        for ($jahr = 2018; $jahr <= 2021; $jahr++) {
            $url = 'https://stadt.muenchen.de/infos/strassenneubenennungen' . $jahr . '-uebersicht.html';
            $txt = RISTools::load_file($url);

            $txt = explode('<ul class="m-linklist__list">', $txt);
            $txt = explode("</ul>", $txt[1]);

            preg_match_all('/<span class="m-linklist-element__title">(?<strasse>[^<]*)<\/span>/siu', $txt[0], $matches);
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
