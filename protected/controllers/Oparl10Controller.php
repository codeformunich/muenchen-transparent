<?php

/**
 * Enhält alle actions für OParl 1.0 sowie einige Hilfsmethoden
 */
class OParl10Controller extends CController {
    const VERSION = 'https://oparl.org/specs/1.0/';

    /**
     * Erzeugt die URL zu einem einzelnen OParl-Objekt
     */
    public static function getOparlObjectUrl($typ, $id, $subtype = null)
    {
        if ($typ == 'system') {
            return OPARL_10_ROOT;
        }

        if ($subtype != null) {
            return OPARL_10_ROOT . '/' . $typ . '/' . $subtype . '/' . $id;
        }

        return OPARL_10_ROOT . '/' . $typ . '/' . $id;
    }

    /**
     * Erzeugt die URL zu einer externen Liste mit OParl-Objekten
     *
     * @param $typ int
     * @param $body int
     * @param $filter OParl10Filter
     *
     * @return String
     */
    public static function getOparlListUrl($typ, $body = null, $filter = null) {
        $url = OPARL_10_ROOT;
        if ($body !== null) $url .= '/body/' . $body;
        $url .= '/list/' . $typ;
        if ($filter != null) $url .= '?' . $filter->to_url_params();

        return $url;
    }

    /**
     * Wandelt einen MySQL timestamp in einen String mit Datum, Zeit und Zeitzone im dem vom OParl genutzten Format um
     * (entspricht ISO 8601)
     */
    public static function mysqlToOparlDateTime($in) {
        return (new DateTime($in, new DateTimeZone(DEFAULT_TIMEZONE)))->format(DateTime::ATOM);
    }

    /**
     * Gibt das Array $data als OParl-Objekt zusammen mit den korrekten Headern aus.
     */
    public static function asOParlJSON($data) {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Gibt das 'oparl:System'-Objekt als JSON aus
     */
    public function actionSystem() {
        self::asOParlJSON(OParl10Object::get('system', null));
    }

    /**
     * Gibt ein beliebiges Objekt außer 'oparl:System' als JSON aus
     */
    public function actionObject($typ, $id, $subtype = null) {
        self::asOParlJSON(OParl10Object::get($typ, $id, $subtype));
    }

    /**
     * Gibt die externen Liste mit den 'oparl:Body'-Objekten als JSON aus
     */
    public function actionExternalListBody() {
        self::asOParlJSON(OParl10List::get('body', null, new OParl10Filter()));
    }

    /**
     * Gibt ein beliebiges Objekt außer 'oparl:System' als JSON aus
     */
    public function actionExternalList($typ, $body, $id = null, $created_since = null, $created_until = null, $modified_since = null, $modified_until = null) {
        $filter = new OParl10Filter();
        $filter->id  = $id;
        $filter->created_since  = $created_since;
        $filter->created_until  = $created_until;
        $filter->modified_since = $modified_since;
        $filter->modified_until = $modified_until;
        self::asOParlJSON(OParl10List::get($typ, $body, $filter));
    }

    /**
     * Gibt die Datei mit zum Dokument $id mit den zu $mode gehörenden headern aus
     */
    public function actionFileaccess($mode, $id) {
        $dokument = Dokument::model()->findByPk($id);
        if ($dokument === null) {
            header('HTTP/1.0 404 Not Found');
            return;
        }

        $content = $dokument->getDateiInhalt();
        if ($content === null) {
            header('HTTP/1.0 410 Gone');
            return;
        } else {
            echo $content;
        }

        if ($mode == 'download') {
            header('Content-Disposition: attachment; filename="' . $dokument->getDateiname() . '"');
        }
    }
}
