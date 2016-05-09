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
     */
    public static function getOparlListUrl($typ, $body = null, $id = null) {
        $url = OPARL_10_ROOT;
        if ($body !== null) $url .= '/body/' . $body;
        $url .= '/list/' . $typ;
        if ($id !== null) $url .= '?id=' . $id;

        return $url;
    }

    /**
     * Erzeugt einen String mit Datum und Zeit im richtigen Format
     */
    public static function toOparlDateTime($in) {
        return (new DateTime($in, new DateTimeZone(DEFAULT_TIMEZONE)))->format(DateTime::ATOM);
    }

    /**
     * Gibt ein Array als OParl-Objekt mit den korrekten Headern aus.
     */
    public static function asOParlJSON($data) {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode($data);
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
        self::asOParlJSON(OParl10List::get('body', null));
    }

    /**
     * Gibt ein beliebiges Objekt außer 'oparl:System' als JSON aus
     */
    public function actionExternalList($typ, $body, $id = null) {
        self::asOParlJSON(OParl10List::get($typ, $body, $id));
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
