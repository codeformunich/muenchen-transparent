<?php

class ImportStatistikCommand extends CConsoleCommand
{
    public function csv_to_array($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data   = array();
        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    public function run($args)
    {
        if (!isset($args[0])) {
            echo "./yiic importstatistik [dateiname]\n";
            die();
        }
        $sql = Yii::app()->db->createCommand();
        $sql->delete('statistik_datensaetze', array("quelle = " . StatistikDatensatz::QUELLE_BEVOELKERUNG));
        $arr = $this->csv_to_array($args[0]);

        $floatize = function($in) {
            if (trim($in) == "") return null;
            return FloatVal(str_replace(",", ".", $in));
        };

        foreach ($arr as $i => $row) {
            if (($i % 100) == 0) echo $i . "\n";
            $dat                        = new StatistikDatensatz();
            $dat->quelle                = StatistikDatensatz::QUELLE_BEVOELKERUNG;
            $dat->indikator_gruppe      = $row["INDIKATOR_GRUPPE"];
            $dat->indikator_bezeichnung = $row["INDIKATOR_BEZEICHNUNG"];
            $dat->indikator_auspraegung = $row["INDIKATOR_AUSPRAEGUNG"];
            $dat->indikator_wert        = $floatize($row["INDIKATOR_WERT"]);
            $dat->basiswert_1           = $floatize($row["BASISWERT_1"]);
            $dat->basiswert_1_name      = $row["NAME_BASISWERT1"];
            $dat->basiswert_2           = $floatize($row["BASISWERT_2"]);
            $dat->basiswert_2_name      = $row["NAME_BASISWERT2"];
            $dat->basiswert_3           = $floatize($row["BASISWERT_3"]);
            $dat->basiswert_3_name      = $row["NAME_BASISWERT3"];
            $dat->basiswert_4           = $floatize($row["BASISWERT_4"]);
            $dat->basiswert_4_name      = $row["NAME_BASISWERT4"];
            $dat->basiswert_5           = $floatize($row["BASISWERT_5"]);
            $dat->basiswert_5_name      = $row["NAME_BASISWERT5"];
            $dat->jahr                  = $row["JAHR"];
            $dat->gliederung            = $row["GLIEDERUNG"];
            $dat->gliederung_nummer     = $row["NUMMER"];
            $dat->gliederung_name       = $row["NAME"];
            if (!$dat->save()) var_dump($dat->getErrors());
        }
    }
}
