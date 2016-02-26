<?php

//define("VERYFAST", true);

class BAGrenzenGeojsonCommand extends CConsoleCommand
{
    public function run($args)
    {
        if (count($args) != 1) die("Usage: ./yiic bagrenzen [Dateiname]\n");

        $BAGrenzenGeoJSON                         = [];
        $BAs                                      = Bezirksausschuss::model()->findAll();
        foreach ($BAs as $ba) $BAGrenzenGeoJSON[] = $ba->toGeoJSONArray();

        file_put_contents($args[0], "BA_GRENZEN_GEOJSON = ".json_encode($BAGrenzenGeoJSON).";\n");
    }
}
