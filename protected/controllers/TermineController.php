<?php

use JetBrains\PhpStorm\ArrayShape;

class TermineController extends RISBaseController
{

    /**
     * @var Termin[] $appointments
     */
    #[ArrayShape(["has_weekend" => "bool", "data" => "array"])]
    private function getFullcalendarStruct(array $appointments): array
    {
        $appointments_data = Termin::groupAppointments($appointments);
        $jsdata            = [];
        $has_weekend       = false;
        foreach ($appointments_data as $appointment) {
            $d        = [
                "title"    => str_replace("Ausschuss für ", "", implode(", ", array_keys($appointment["gremien"]))),
                "start"    => str_replace(" ", "T", $appointment["datum_iso"]),
                "url"      => $appointment["link"],
                "canceled" => $appointment["abgesagt"],
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


    #[ArrayShape(["has_weekend" => "bool", "data" => "array"])]
    private function getFullCalendarStructByMonth(int $year, int $month, int $margin_days = 7): array
    {
        $dateFrom = (new \DateTime())->setTime(0, 0, 0)->setDate($year, $month, 1)->modify('-' . $margin_days . ' days');
        $dateTo = (new \DateTime())->setTime(0, 0, 0)->setDate($year, $month, 1)->modify('+1 month')->modify('+' . $margin_days . ' days');

        /** @var Termin[] $termine_monat */
        $termine_monat       = Termin::model()->termine_stadtrat_zeitraum(null, $dateFrom, $dateTo, true)->findAll();
        return $this->getFullcalendarStruct($termine_monat);
    }

    public function actionFullCalendarFeed(string $start, string $end): void
    {
        $start = new DateTime($start);
        $end = new DateTime($end);

        /** @var Termin[] $termine_monat */
        $termine_monat       = Termin::model()->termine_stadtrat_zeitraum(null, $start, $end, true)->findAll();
        $fullcalendar_struct = $this->getFullcalendarStruct($termine_monat);
        Header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($fullcalendar_struct["data"]);
        Yii::app()->end();
    }


    public function actionIndex()
    {
        $this->top_menu      = "termine";

        $tage_zukunft       = 30;
        $tage_vergangenheit = 30;

        $dateNow = (new \DateTime())->setTime(0, 0, 0);

        $termine_zukunft       = Termin::model()->termine_stadtrat_zeitraum(null, $dateNow, (clone $dateNow)->modify('+24 days'), true)->findAll();
        $termine_vergangenheit = Termin::model()->termine_stadtrat_zeitraum(null, (clone $dateNow)->modify('-24 days'), $dateNow, false)->findAll();
        $termin_dokumente      = Termin::model()->neueste_str_protokolle(0, date("Y-m-d 00:00:00", time() - 60 * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();
        /** @var Termin[] $termine_zukunft */
        /** @var Termin[] $termine_vergangenheit */
        /** @var Termin[] $termin_dokumente */
        $gruppiert_zukunft       = Termin::groupAppointments($termine_zukunft);
        $gruppiert_vergangenheit = Termin::groupAppointments($termine_vergangenheit);

        $fullcalendar_struct = $this->getFullCalendarStructByMonth(intval(date("Y")), intval(date("m")));

        $this->render("index", [
            "termine_zukunft"       => $gruppiert_zukunft,
            "termine_vergangenheit" => $gruppiert_vergangenheit,
            "termin_dokumente"      => $termin_dokumente,
            "fullcalendar_struct"   => $fullcalendar_struct,
            "tage_vergangenheit"    => $tage_vergangenheit,
            "tage_zukunft"          => $tage_zukunft,
        ]);
    }

    public function actionAnzeigen(string $termin_id): void
    {
        $termin_id = intval($termin_id);

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

    public function actionBaZukunft(): void
    {
        $sql = Yii::app()->db->createCommand();
        $sql->select('a.*, b.name')->from('termine a')->join('gremien b', 'a.gremium_id = b.id')->where('b.ba_nr > 0')->andWhere('b.name LIKE "%Voll%"')->andWhere("a.termin >= CURRENT_DATE()")->order("termin");
        $termine = $sql->queryAll();
        $this->render("ba_zukunft_html", [
            "termine" => $termine
        ]);
    }

    public function actionBaZukunftCsv(): void
    {
        $sql = Yii::app()->db->createCommand();
        $sql->select('a.*, b.name')->from('termine a')->join('gremien b', 'a.gremium_id = b.id')->where('b.ba_nr > 0')->andWhere('b.name LIKE "%Voll%"')->andWhere("a.termin >= CURRENT_DATE()")->order("termin");
        $termine = $sql->queryAll();
        $this->renderPartial("ba_zukunft_csv", [
            "termine" => $termine
        ]);
    }

    public function actionBaTermineAlle($ba_nr): void
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
