<?php

namespace app\controllers;

use Yii;
use app\components\RISBaseController;
use app\models\Bezirksausschuss;
use app\models\Termin;
use yii\helpers\Url;

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
        $termine_monat       = Termin::find()->termine_stadtrat_zeitraum(null, $margin_start, $margin_end, true)->findAll();
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
        $termine_monat       = Termin::find()->termine_stadtrat_zeitraum(null, $start, $end, true)->findAll();
        $fullcalendar_struct = $this->getFullcalendarStruct($termine_monat);
        Header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($fullcalendar_struct["data"]);
        Yii::$app->end();
    }


    /**
     *
     */
    public function actionIndex()
    {
        $this->top_menu      = "termine";
        $this->load_calendar = true;

        $tage_zukunft       = 30;
        $tage_vergangenheit = 30;

        $termine_zukunft       = Termin::find()->termine_stadtrat_zeitraum(null, date("Y-m-d 00:00:00", time()), date("Y-m-d 00:00:00", time() + $tage_zukunft * 24 * 3600), true)->findAll();
        $termine_vergangenheit = Termin::find()->termine_stadtrat_zeitraum(null, date("Y-m-d 00:00:00", time() - $tage_vergangenheit * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();
        $termin_dokumente      = Termin::find()->neueste_str_protokolle(0, date("Y-m-d 00:00:00", time() - 60 * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();
        /** @var Termin[] $termine_zukunft */
        /** @var Termin[] $termine_vergangenheit */
        /** @var Termin[] $termin_dokumente */
        $gruppiert_zukunft       = Termin::groupAppointments($termine_zukunft);
        $gruppiert_vergangenheit = Termin::groupAppointments($termine_vergangenheit);

        $fullcalendar_struct = $this->getFullCalendarStructByMonth(date("Y"), date("m"));

        return $this->render("index", [
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
        $termin = Termin::findOne($termin_id);
        if (!$termin) {
            return $this->render('/index/error', ["code" => 404, "message" => "Der Termin wurde nicht gefunden"]);
            return;
        }

        if (count($termin->tagesordnungspunkte) > 0) {
            $this->load_leaflet_css = true;
            $to_pdf                 = null;
            $to_db                  = $termin->tagesordnungspunkte;
        } else {
            $to = $termin->errateAktuellsteTagesordnung();
            if ($to) {
                $this->load_pdf_js = true;
                $to_pdf            = $to;
                $to_db             = null;
            } else {
                $to_pdf = null;
                $to_db  = null;
            }
        }

        return $this->render("anzeige", [
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
        $termin = Termin::findOne($termin_id);
        if (!$termin) {
            return $this->render('/index/error', ["code" => 404, "message" => "Der Termin wurde nicht gefunden"]);
            return;
        }

        echo $this->render("ics_all", [
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
        $termin = Termin::findOne($termin_id);
        if (!$termin) {
            return $this->render('/index/error', ["code" => 404, "message" => "Der Termin wurde nicht gefunden"]);
            return;
        }

        echo $this->render("ics_single", [
            "termin" => $termin,
        ]);
    }

    /**
     */
    public function actionBaZukunft()
    {
        $sql = Yii::$app->db->createCommand();
        $sql->select('a.*, b.name')->from('termine a')->join('gremien b', 'a.gremium_id = b.id')->where('b.ba_nr > 0')->andWhere('b.name LIKE "%Voll%"')->andWhere("a.termin >= CURRENT_DATE()")->order("termin");
        $termine = $sql->queryAll();
        return $this->render("ba_zukunft_html", [
            "termine" => $termine
        ]);
    }
    /**
     */
    public function actionBaZukunftCsv()
    {
        $sql = Yii::$app->db->createCommand();
        $sql->select('a.*, b.name')->from('termine a')->join('gremien b', 'a.gremium_id = b.id')->where('b.ba_nr > 0')->andWhere('b.name LIKE "%Voll%"')->andWhere("a.termin >= CURRENT_DATE()")->order("termin");
        $termine = $sql->queryAll();
        echo $this->render("ba_zukunft_csv", [
            "termine" => $termine
        ]);
    }

    /**
     * @param int $termin_id
     */
    public function actionAboInfo($termin_id)
    {
        /** @var Termin $sitzung */
        $termin = Termin::findOne($termin_id);
        if (!$termin) {
            return $this->render('/index/error', ["code" => 404, "message" => "Der Termin wurde nicht gefunden"]);
            return;
        }

        return $this->render("abo_info", [
            "termin" => $termin,
        ]);
    }


    /**
     * @param int $termin_id
     */
    public function actionDav($termin_id)
    {
        $DEBUG = false;

        $principalBackend = new TermineCalDAVPrincipalBackend($termin_id);
        $calendarBackend  = new TermineCalDAVCalendarBackend($termin_id);

        $tree = [
            new Sabre\CalDAV\Principal\Collection($principalBackend),
            new Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend),
        ];

        $server = new TermineCalDAVServerBugfix($tree);
        $server->setBaseUri(Url::to("termine/dav", ["termin_id" => $termin_id]));

        $authBackend = new TermineCalDAVAuthBackend();
        $authPlugin  = new \Sabre\DAV\Auth\Plugin($authBackend, 'SabreDAV');
        $server->addPlugin($authPlugin);

        $server->addPlugin(new Sabre\CalDAV\Plugin());
        $server->addPlugin(new Sabre\DAV\Browser\Plugin());
        $server->addPlugin(new Sabre\DAVACL\Plugin());

        if ($DEBUG) {
            $server->addPlugin(new TermineCalDAVDebug(TMP_PATH . "sabredav.log"));

            ob_start();
            $server->exec();
            $txt = ob_get_flush();
            $fp  = fopen(TMP_PATH . "sabredav.log", "a");
            fwrite($fp, "RESPONSE:\n\n" . $txt . "\n\n=============================\n\n");
            fclose($fp);
        } else {
            $server->exec();
        }
    }

    public function actionBaTermineAlle($ba_nr)
    {
        /** @var Termin[] $termine */
        $termine    = Termin::find()->all(["ba_nr" => $ba_nr])->orderBy(['termin' => SORT_DESC]);
        $termin_arr = [];
        foreach ($termine as $t) {
            $termin_arr[] = $t->toArr();
        }

        /** @var Bezirksausschuss $ba */
        $ba = Bezirksausschuss::findOne($ba_nr);

        return $this->render("ba_termine_alle", [
            "ba"      => $ba,
            "termine" => $termin_arr,
        ]);
    }


}
