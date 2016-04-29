<?php

/**
 * Enhält alle actions für OParl 1.0, Hilfmethoden zum Erzeugen von OParl-URLs
 * sowie Methoden zum Erzeugen externer Listen
 */
class OParl10Controller extends CController {
    const VERSION = 'https://oparl.org/specs/1.0/';

    const ITEMS_PER_PAGE = OPARL_10_ITEMS_PER_PAGE;

    /*
     * Erzeugt die URL zu einem einzelnen OParl-Objekt
     */
    public static function getOparlObjectUrl($typ, $id = null) {
        if ($typ == 'system') {
            return OPARL_10_ROOT;
        }

        $url = OPARL_10_ROOT . '/' . $typ . '/' . $id;

        return $url;
    }

    /*
     * Erzeugt die URL zu einer externen Liste mit OParl-Objekten
     */
    public static function getOparlListUrl($typ, $body = null, $id = null) {
        $url = OPARL_10_ROOT;
        if ($body !== null) $url .= '/body/' . $body;
        $url .= '/list/' . $typ;
        if ($id !== null) $url .= '?id=' . $id;

        return $url;
    }

    /*
     * Erzeugt einen String mit Datum und Zeit im richtigen Format
     */
    public static function oparlDateTime($in) {
        return date(DateTime::ATOM, strtotime($in));
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
    public function actionListBody() {
        Header('Content-Type: application/json');

        $bodies = [OParl10Object::object('body', 0)];

        $bas = Bezirksausschuss::model()->findAll();
        foreach ($bas as $ba)
            $bodies[] = OParl10Object::object('body', $ba->ba_nr);

        echo json_encode([
            'items'         => $bodies,
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => static::getOparlListUrl('body'),
            'lastPage'      => static::getOparlListUrl('body'),
            'numberOfPages' => 1,
        ]);
    }

    /**
     * Die externe Objektliste mit allen 'oparl:Organization'-Objekten, d.h.
     * - die Gremien
     * - die Frakionen
     * - nur beim Stadtrat: die Referate
     */
    public function actionListOrganization($body) {
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

        if ($body == 0) {
            $referate = Referat::model()->findAll();
            foreach ($referate as $referat)
                $organizations[] = OParl10Object::object('organization_referat', $referat->id);
        }

        echo json_encode([
            'items'         => $organizations,
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => static::getOparlListUrl('organization', $body),
            'lastPage'      => static::getOparlListUrl('organization', $body),
            'numberOfPages' => 1,
        ]);
    }

    /*
     * Eine allgemeine externe Objektliste, die für verschiedene Objekte genutzt werden kann
     */
    public static function externalList($model, $name, $body, $ba_check, $id = null) {
        Header('Content-Type: application/json');

        $criteria = new CDbCriteria();

        // TODO: Nur die opal:person-Objekte des gewählten Bodies ausgeben
        if ($ba_check) {
            if ($body > 0) {
                $criteria->addCondition('ba_nr = :ba_nr');
                $criteria->params["ba_nr"] = $body;
            } else {
                $criteria->addCondition('ba_nr IS NULL');
            }
        }

        $count = $model->count($criteria);

        // Stabile Paginierung: Nur eine bestimmte Anzahl an Elementen ausgeben, deren id größer als $id ist
        $criteria->order = 'id ASC';
        $criteria->limit = static::ITEMS_PER_PAGE;
        if ($id !== null) {
            $criteria->addCondition('id > :id');
            $criteria->params["id"] = $id;
        }

        $entries = $model->findAll($criteria);
        $oparl_entries = [];
        foreach ($entries as $entry)
            $oparl_entries[] = OParl10Object::object($name, $entry->id);

        $last_entry = $model->find(['order' => 'id DESC']);

        $data = [
            'items'         => $oparl_entries,
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => static::getOparlListUrl($name, $body),
            'numberOfPages' => ceil($count / static::ITEMS_PER_PAGE),
        ];

        if (count($entries) > 0 && end($entries)->id != $last_entry->id)
            $data['nextPage'] = static::getOparlListUrl($name, $body, end($entries)->id);

        echo json_encode($data);
    }

    /**
     * Die externe Objektliste mit allen 'oparl:person'-Objekten
     */
    public function actionListPerson($body, $id = null) {
        self::externalList(StadtraetIn::model(), 'person', $body, false, $id);
    }

    /**
     * Die externe Objektliste mit allen 'oparl:meeting'-Objekten
     */
    public function actionListMeeting($body, $id = null) {
        self::externalList(Termin::model(), 'meeting', $body, true, $id);
    }

    /**
     * Die externe Objektliste mit allen 'oparl:paper'-Objekten
     */
    public function actionListPaper($body, $id = null) {
        self::externalList(Antrag::model(), 'paper', $body, true, $id);
    }

    /**
     * Die externe Objektliste mit allen 'oparl:LegislativeTerm'-Objekten
     */
    public function actionListTerm($body) {
        Header('Content-Type: application/json');

        echo json_encode([
            'items'         => OParl10Object::terms(-1),
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => static::getOparlListUrl('term', $body),
            'lastPage'      => static::getOparlListUrl('term', $body),
            'numberOfPages' => 1,
        ]);
    }

}
