<?php

/**
 * Lädt alle Datensätze des Indikatorenatlases über die API des Open-Data-Portals der Stadt München herunter und
 * importiert sie in die Datenbank
 */
class ImportStatistikCommand extends CConsoleCommand
{
    static private $source = 'https://www.opengov-muenchen.de/api/3/action/tag_show?id=Indikatorenatlas';

    /** Die csv-Dateien enthalten deutsche Kommazahlen, die in floating-point numbers umgewandelt werden müssen */
    public function floatize ($in) {
        return trim($in) == "" ? null : floatval(str_replace(",", ".", $in));
    }

    public function run($args)
    {
        // Etwas eigene Statistik
        $start_time = microtime(true);
        $rows_imported = 0;

        // Alle alten Datensätze des Indikatorenatlases löschen, damit es zu keinen Kollisionen kommt
        $sql = Yii::app()->db->createCommand();
        $sql->delete('statistik_datensaetze', ['quelle = ' . StatistikDatensatz::QUELLE_INDIKATORENATLAS]);

        $json = json_decode(file_get_contents(self::$source));
        foreach ($json->result->packages as $indikatorenatlas) {
            echo $indikatorenatlas->title . "\n";
            $csv = array_map('str_getcsv', file($indikatorenatlas->resources[0]->url));
            $header = array_flip(array_shift($csv));
            $rows_imported += sizeof($csv);

            foreach ($csv as $row) {
                $dat = new StatistikDatensatz();
                $dat->quelle = StatistikDatensatz::QUELLE_INDIKATORENATLAS;

                $dat->indikator_gruppe      =                 $row[$header['INDIKATOR_GRUPPE'     ]];
                $dat->indikator_bezeichnung =                 $row[$header['INDIKATOR_BEZEICHNUNG']];
                $dat->indikator_auspraegung =                 $row[$header['INDIKATOR_AUSPRAEGUNG']];
                $dat->indikator_wert        = $this->floatize($row[$header['INDIKATOR_WERT'       ]]);
                $dat->basiswert_1           = $this->floatize($row[$header['BASISWERT_1'          ]]);
                $dat->basiswert_1_name      =                 $row[$header['NAME_BASISWERT_1'     ]];
                $dat->basiswert_2           = $this->floatize($row[$header['BASISWERT_2'          ]]);
                $dat->basiswert_2_name      =                 $row[$header['NAME_BASISWERT_2'     ]];
                $dat->basiswert_3           = $this->floatize($row[$header['BASISWERT_3'          ]]);
                $dat->basiswert_3_name      =                 $row[$header['NAME_BASISWERT_3'     ]];
                $dat->basiswert_4           = $this->floatize($row[$header['BASISWERT_4'          ]]);
                $dat->basiswert_4_name      =                 $row[$header['NAME_BASISWERT_4'     ]];
                $dat->basiswert_5           = $this->floatize($row[$header['BASISWERT_5'          ]]);
                $dat->basiswert_5_name      =                 $row[$header['NAME_BASISWERT_5'     ]];
                $dat->jahr                  =                 $row[$header['JAHR'                 ]];
                $dat->gliederung            =                 $row[$header['GLIEDERUNG'           ]];
                $dat->gliederung_nummer     =                 $row[$header['NUMMER'               ]];
                $dat->gliederung_name       =                 $row[$header['NAME'                 ]];

                if (!$dat->save()) var_dump($dat->getErrors());
            }
        }

        $elapsed = microtime(true) - $start_time;
        echo $rows_imported . " entries have been imported  in " . $elapsed . " seconds\n";
    }
}
