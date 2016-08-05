<?php

class Recalc_Ort2BACommand extends CConsoleCommand
{
    public function run($args)
    {
        if (count($args) == 0) die("./yii recalc_ort2ba [Orts-ID|alle]\n");

        if ($args[0] == "alle") {
            /** @var OrtGeo[] $orte */
            $orte = OrtGeo::model()->findAll(["order" => "id"]);
        } else {
            /** @var OrtGeo[] $orte */
            $orte = OrtGeo::model()->findAll(["condition" => "id = " . IntVal($args[0])]);
        }


        /** @var Bezirksausschuss[] $bas */
        $bas = Bezirksausschuss::model()->alleOhneStadtrat();

        foreach ($orte as $ort) {
            $found_ba = null;
            foreach ($bas as $ba) if ($ba->pointInBA($ort->lon, $ort->lat)) {
                echo $ort->id . " - " . $ort->ort . ": " . $ba->ba_nr . "\n";
                $found_ba = $ba->ba_nr;
            }
            if ($found_ba) {
                $ort->ba_nr = $found_ba;
                $ort->save();
            }
        }
    }
}