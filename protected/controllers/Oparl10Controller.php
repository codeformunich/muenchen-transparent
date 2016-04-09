<?php

class OParl10Controller extends CController {
    const VERSION = 'https://oparl.org/specs/1.0/';

    const ITEMS_PER_PAGE = 100;

    /*
     * Erzeugt die URL zu einem einzelnen OParl-Objekt
     */
    public static function getOparlObjectUrl($typ, $body = null, $id = null) {
        if ($body == null) $body = 0;

        if ($typ == 'system') {
            return OPARL_10_ROOT;
        } else if ($typ == 'body') {
            return OPARL_10_ROOT . '/body/' . $body;
        }

        $url = OPARL_10_ROOT . '/body/' . $body . '/' . $typ . '/' . $id;

        return $url;
    }

    /*
     * Erzeugt die URL zu einer externen Objektliste
     */
    public static function getOparlListUrl($typ, $body = null, $id = null) {
        $url = OPARL_10_ROOT;
        if ($body !== null) $url .= '/body/' . $body;
        $url .= '/' . $typ;
        if ($id !== null) $url .= '/' . $id;

        return $url;
    }

    /*
     * Gibt das 'oparl:System'-Objekt als JSON aus
     */
    public function actionSystem() {
        Header('Content-Type: application/json');
        echo json_encode(OParl10Object::object('system', null));
    }

    /*
     * Gibt ein beliebiges Objekt außer 'oparl:System' als JSON aus
     */
    public function actionObject($typ, $id) {
        Header('Content-Type: application/json');
        echo json_encode(OParl10Object::object($typ, $id));
    }

    /**
     * Die externe Objektliste mit allen 'oparl:Body'-Objekten
     */
    public function actionBodies() {
        Header('Content-Type: application/json');

        $bodies = [OParl10Object::object('body', 0)];

        $bas = Bezirksausschuss::model()->findAll();
        foreach ($bas as $ba)
            $bodies[] = OParl10Object::object('body', $ba->ba_nr);

        echo json_encode([
            'items'         => $bodies,
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => static::getOparlListUrl('bodies'),
            'lastPage'      => static::getOparlListUrl('bodies'),
            'numberOfPages' => 1,
        ]);
    }

    /**
     * Die externe Objektliste mit allen 'oparl:Organization'-Objekten
     */
    public function actionOrganizations($body) {
        Header('Content-Type: application/json');

        // FIXME: https://github.com/codeformunich/Muenchen-Transparent/issues/135
        $query = ($body > 0 ? 'ba_nr = ' . $body : 'ba_nr IS NULL');

        $organizations = [];

        $gremien = Gremium::model()->findAll($query);
        foreach ($gremien as $gremium)
            $organizations[] = OParl10Object::object('organization_gremium', $gremium->id);

        $fraktionen = Fraktion::model()->findAll($query);
        foreach ($fraktionen as $fraktion)
            $organizations[] = OParl10Object::object('organization_fraktion', $fraktion->id);

        echo json_encode([
            'items'         => $organizations,
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => static::getOparlListUrl('organizations', $body),
            'lastPage'      => static::getOparlListUrl('organizations', $body),
            'numberOfPages' => 1,
        ]);
    }

    /**
     * Die externe Objektliste mit allen 'oparl:LegislativeTerm'-Objekten
     */
    public function actionTerms($body) {
        Header('Content-Type: application/json');

        echo json_encode([
            'items'         => OParl10Object::terms($body),
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => static::getOparlListUrl('bodies'),
            'lastPage'      => static::getOparlListUrl('bodies'),
            'numberOfPages' => 1,
        ]);
    }

}
