<?php

/**
 * Lädt alle Datensätze des Indikatorenatlases über die API des Open-Data-Portals der Stadt München herunter und
 * importiert sie in die Datenbank
 */
class ImportStatistikCommand extends CConsoleCommand
{
    static private $source = 'https://www.opengov-muenchen.de/api/3/action/tag_show?id=Indikatorenatlas';

    public function run($args)
    {
        $start_time = microtime(true);

        $json = json_decode(file_get_contents(self::$source));

        $sql = Yii::app()->db->createCommand();
        $sql->delete('statistik_datensaetze', ['quelle = ' . StatistikDatensatz::QUELLE_INDIKATORENATLAS]);

        $lines_imported = 0;
        foreach ($json->result->packages as $package) {
            echo $package->title . "\n";
            $csv = array_map('str_getcsv', file($package->resources[0]->url));
            $header = array_flip(array_shift($csv));
            $lines_imported += sizeof($csv);


            $floatize = function ($in) {
                if (trim($in) == "") return null;
                return FloatVal(str_replace(",", ".", $in));
            };

            foreach ($csv as $row) {
                $dat = new StatistikDatensatz();
                $dat->quelle = StatistikDatensatz::QUELLE_INDIKATORENATLAS;

                $dat->indikator_gruppe      =           $row[$header['INDIKATOR_GRUPPE'     ]];
                $dat->indikator_bezeichnung =           $row[$header['INDIKATOR_BEZEICHNUNG']];
                $dat->indikator_auspraegung =           $row[$header['INDIKATOR_AUSPRAEGUNG']];
                $dat->indikator_wert        = $floatize($row[$header['INDIKATOR_WERT'       ]]);
                $dat->basiswert_1           = $floatize($row[$header['BASISWERT_1'          ]]);
                $dat->basiswert_1_name      =           $row[$header['NAME_BASISWERT_1'     ]];
                $dat->basiswert_2           = $floatize($row[$header['BASISWERT_2'          ]]);
                $dat->basiswert_2_name      =           $row[$header['NAME_BASISWERT_2'     ]];
                $dat->basiswert_3           = $floatize($row[$header['BASISWERT_3'          ]]);
                $dat->basiswert_3_name      =           $row[$header['NAME_BASISWERT_3'     ]];
                $dat->basiswert_4           = $floatize($row[$header['BASISWERT_4'          ]]);
                $dat->basiswert_4_name      =           $row[$header['NAME_BASISWERT_4'     ]];
                $dat->basiswert_5           = $floatize($row[$header['BASISWERT_5'          ]]);
                $dat->basiswert_5_name      =           $row[$header['NAME_BASISWERT_5'     ]];
                $dat->jahr                  =           $row[$header['JAHR'                 ]];
                $dat->gliederung            =           $row[$header['GLIEDERUNG'           ]];
                $dat->gliederung_nummer     =           $row[$header['NUMMER'               ]];
                $dat->gliederung_name       =           $row[$header['NAME'                 ]];

                if (!$dat->save()) var_dump($dat->getErrors());
            }
        }

        $elapsed = microtime(true) - $start_time;
        echo $lines_imported . " entries have been imported  in " . $elapsed . " seconds\n";
    }
}
