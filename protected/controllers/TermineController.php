<?php

class TermineController extends RISBaseController
{

    /**
     * @var Termin[] $appointments
     * @return array
     */
    private function getFullcalendarStruct($appointments)
    {
        $appointments_data = Termin::groupAppointments($appointments);
        $jsdata            = [];
        $has_weekend       = false;
        foreach ($appointments_data as $appointment) {
            $d        = [
                "title"    => str_replace("Ausschuss für ", "", implode(", ", array_keys($appointment["gremien"]))),
                "start"    => str_replace(" ", "T", $appointment["datum_iso"]),
                "url"      => $appointment["link"],
                "abgesagt" => $appointment["abgesagt"],
            ];
            $jsdata[] = $d;
            $weekday  = date("N", $appointment["datum_ts"]);
            if ($weekday == 6 || $weekday == 7) {
                $has_weekend = true;
            }
        }
        return [
            "has_weekend" => $has_weekend,
            "data"        => $jsdata,
        ];
    }


    /**
     * @param int $year
     * @param int $month
     * @param int $margin_days
     * @return array
     */
    private function getFullCalendarStructByMonth($year, $month, $margin_days = 7)
    {
        $ts_start   = mktime(0, 0, 0, $month, 1, $year);
        $monat_tage = date("t", $ts_start);
        $ts_end     = mktime(0, 0, 0, $month, $monat_tage, $year);

        $margin_start = date("Y-m-d", $ts_start - $margin_days * 24 * 3600);
        $margin_end   = date("Y-m-d", $ts_end + $margin_days * 24 * 3600);

        /** @var Termin[] $termine_monat */
        $termine_monat       = Termin::model()->termine_stadtrat_zeitraum(null, $margin_start, $margin_end, true)->findAll();
        $fullcalendar_struct = $this->getFullcalendarStruct($termine_monat);
        return $fullcalendar_struct;
    }

    /**
     * @param string $start
     * @param string $end
     */
    public function actionFullCalendarFeed($start, $end)
    {
        /** @var Termin[] $termine_monat */
        $termine_monat       = Termin::model()->termine_stadtrat_zeitraum(null, $start, $end, true)->findAll();
        $fullcalendar_struct = $this->getFullcalendarStruct($termine_monat);
        Header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($fullcalendar_struct["data"]);
        Yii::app()->end();
    }


    /**
     *
     */
    public function actionIndex()
    {
        $this->top_menu      = "termine";

        $tage_zukunft       = 30;
        $tage_vergangenheit = 30;

        $termine_zukunft       = Termin::model()->termine_stadtrat_zeitraum(null, date("Y-m-d 00:00:00", time()), date("Y-m-d 00:00:00", time() + $tage_zukunft * 24 * 3600), true)->findAll();
        $termine_vergangenheit = Termin::model()->termine_stadtrat_zeitraum(null, date("Y-m-d 00:00:00", time() - $tage_vergangenheit * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();
        $termin_dokumente      = Termin::model()->neueste_str_protokolle(0, date("Y-m-d 00:00:00", time() - 60 * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();
        /** @var Termin[] $termine_zukunft */
        /** @var Termin[] $termine_vergangenheit */
        /** @var Termin[] $termin_dokumente */
        $gruppiert_zukunft       = Termin::groupAppointments($termine_zukunft);
        $gruppiert_vergangenheit = Termin::groupAppointments($termine_vergangenheit);

        $fullcalendar_struct = $this->getFullCalendarStructByMonth(date("Y"), date("m"));

        $this->render("index", [
            "termine_zukunft"       => $gruppiert_zukunft,
            "termine_vergangenheit" => $gruppiert_vergangenheit,
            "termin_dokumente"      => $termin_dokumente,
            "fullcalendar_struct"   => $fullcalendar_struct,
            "tage_vergangenheit"    => $tage_vergangenheit,
            "tage_zukunft"          => $tage_zukunft,
        ]);
    }

    /**
     * @param int $termin_id
     */
    public function actionAnzeigen($termin_id)
    {
        $termin_id = IntVal($termin_id);

        $this->top_menu = "termine";

        /** @var Termin $termin */
        $termin = Termin::model()->findByPk($termin_id);
        if (!$termin) {
            $this->render('/index/error', ["code" => 404, "message" => "Der Termin wurde nicht gefunden"]);
            return;
        }

        $to_pdf = null;
        $to_db  = null;
        if (count($termin->tagesordnungspunkte) > 0) {
            $to_db = $termin->tagesordnungspunkte;
        } else {
            $to_pdf = $termin->errateAktuellsteTagesordnung();
        }

        $this->render("anzeige", [
            "termin" => $termin,
            "to_pdf" => $to_pdf,
            "to_db"  => $to_db,
        ]);
    }


    /**
     * @param int $termin_id
     */
    public function actionIcsExportAll($termin_id)
    {
        $termin_id = IntVal($termin_id);

        /** @var Termin $termin */
        $termin = Termin::model()->findByPk($termin_id);
        if (!$termin) {
            $this->render('/index/error', ["code" => 404, "message" => "Der Termin wurde nicht gefunden"]);
            return;
        }

        $this->renderPartial("ics_all", [
            "alle_termine" => $termin->alleTermineDerReihe(),
        ]);
    }

    /**
     * @param int $termin_id
     */
    public function actionIcsExportSingle($termin_id)
    {
        $termin_id = IntVal($termin_id);

        /** @var Termin $termin */
        $termin = Termin::model()->findByPk($termin_id);
        if (!$termin) {
            $this->render('/index/error', ["code" => 404, "message" => "Der Termin wurde nicht gefunden"]);
            return;
        }

        $this->renderPartial("ics_single", [
            "termin" => $termin,
        ]);
    }

    /**
     */
    public function actionBaZukunft()
    {
        $sql = Yii::app()->db->createCommand();
        $sql->select('a.*, b.name')->from('termine a')->join('gremien b', 'a.gremium_id = b.id')->where('b.ba_nr > 0')->andWhere('b.name LIKE "%Voll%"')->andWhere("a.termin >= CURRENT_DATE()")->order("termin");
        $termine = $sql->queryAll();
        $this->render("ba_zukunft_html", [
            "termine" => $termine
        ]);
    }
    /**
     */
    public function actionBaZukunftCsv()
    {
        $sql = Yii::app()->db->createCommand();
        $sql->select('a.*, b.name')->from('termine a')->join('gremien b', 'a.gremium_id = b.id')->where('b.ba_nr > 0')->andWhere('b.name LIKE "%Voll%"')->andWhere("a.termin >= CURRENT_DATE()")->order("termin");
        $termine = $sql->queryAll();
        $this->renderPartial("ba_zukunft_csv", [
            "termine" => $termine
        ]);
    }

    public function actionBaTermineAlle($ba_nr)
    {
        /** @var Termin[] $termine */
        $termine    = Termin::model()->findAllByAttributes(["ba_nr" => $ba_nr], ["order" => "termin DESC"]);
        $termin_arr = [];
        foreach ($termine as $t) {
            $termin_arr[] = $t->toArr();
        }

        /** @var Bezirksausschuss $ba */
        $ba = Bezirksausschuss::model()->findByPk($ba_nr);

        $this->render("ba_termine_alle", [
            "ba"      => $ba,
            "termine" => $termin_arr,
        ]);
    }


}
