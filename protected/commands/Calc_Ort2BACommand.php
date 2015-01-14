<?php

class Calc_Ort2BACommand extends CConsoleCommand
{
    public function run($args)
    {
        /** @var OrtGeo[] $orte */
        $orte = OrtGeo::model()->findAll(array("condition" => "id >= 35000 AND id < 40000"));

        /** @var Bezirksausschuss[] $bas */
        $bas = Bezirksausschuss::model()->findAll();

        foreach ($orte as $ort) {
            $found_ba = null;
            foreach ($bas as $ba) if ($found_ba === null && $ba->pointInBA($ort->lon, $ort->lat)) {
                echo $ort->ort . ": " . $ba->ba_nr . "\n";
                $found_ba = $ba->ba_nr;
            }
            if ($found_ba) {
                $ort->ba_nr = $found_ba;
                $ort->save();
            }
            echo ".";
        }
    }
}