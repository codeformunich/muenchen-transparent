<?php

/**
 * Kapselt die Funktionen zum Erzeugen externer OParl-Listen
 */
class OParl10List
{
    const ITEMS_PER_PAGE = OPARL_10_ITEMS_PER_PAGE;

    /**
     * Gibt eine beliebiges externe OParl-Objektliste als array zurück
     */
    public static function get($typ, $body, $id = null) {
        if      ($typ == 'body'           ) return self::body($body);
        else if ($typ == 'organization'   ) return self::organization($body);
        else if ($typ == 'person'         ) return self::externalList($body, StadtraetIn::model(), $typ, false, $id);
        else if ($typ == 'meeting'        ) return self::externalList($body, Termin::model(), $typ, true, $id);
        else if ($typ == 'paper'          ) return self::externalList($body, Antrag::model(), $typ, true, $id);
        else if ($typ == 'legislativeterm') return self::legislativeTerm($body);
        else {
            header('HTTP/1.0 400 Bad Request');
            return ['error' => 'No external list for type ' . $typ];
        }
    }

    /**
     * Die externe Objektliste mit allen 'oparl:Body'-Objekten
     */
    private static function body($body)
    {
        $bodies = [OParl10Object::get('body', 0)];

        $bas = Bezirksausschuss::model()->findAll();
        foreach ($bas as $ba)
            $bodies[] = OParl10Object::get('body', $ba->ba_nr);

        return [
            'items'         => $bodies,
            'firstPage'     => OParl10Controller::getOparlListUrl('body'),
            'lastPage'      => OParl10Controller::getOparlListUrl('body'),
            'numberOfPages' => 1,
        ];
    }

    /**
     * Eine allgemeine externe Objektliste, die für verschiedene Objekte genutzt werden kann.
     * In Moment sind das:
     *  - person
     *  - meeting
     *  - paper
     */
    private static function externalList($body, $model, $name, $ba_check, $id = null)
    {
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
            $oparl_entries[] = OParl10Object::get($name, $entry->id);

        $last_entry = $model->find(['order' => 'id DESC']);

        $data = [
            'items'         => $oparl_entries,
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => OParl10Controller::getOparlListUrl($name, $body),
            'numberOfPages' => ceil($count / static::ITEMS_PER_PAGE),
        ];

        if (count($entries) > 0 && end($entries)->id != $last_entry->id)
            $data['nextPage'] = OParl10Controller::getOparlListUrl($name, $body, end($entries)->id);

        return $data;
    }

    /**
     * Die externe Objektliste mit allen 'oparl:LegislativeTerm'-Objekten
     */
    private static function legislativeTerm($body)
    {
        return [
            'items'         => OParl10Object::get("legislativeterm", -1),
            'firstPage'     => OParl10Controller::getOparlListUrl('legislativeterm', $body),
            'lastPage'      => OParl10Controller::getOparlListUrl('legislativeterm', $body),
            'numberOfPages' => 1,
        ];
    }

    /**
     * Die externe Objektliste mit allen 'oparl:Organization'-Objekten, d.h.
     * - die Gremien
     * - die Frakionen
     * - nur beim Stadtrat: die Referate
     */
    private static function organization($body)
    {
        // FIXME: https://github.com/codeformunich/Muenchen-Transparent/issues/135
        $query = ($body > 0 ? 'ba_nr = ' . $body : 'ba_nr IS NULL');

        $organizations = [];

        $gremien = Gremium::model()->findAll($query);
        foreach ($gremien as $gremium)
            $organizations[] = OParl10Object::get('organization', $gremium->id, 'gremium');

        $fraktionen = Fraktion::model()->findAll($query);
        foreach ($fraktionen as $fraktion)
            $organizations[] = OParl10Object::get('organization', $fraktion->id, 'fraktion');

        if ($body == 0) {
            $referate = Referat::model()->findAll();
            foreach ($referate as $referat)
                $organizations[] = OParl10Object::get('organization', $referat->id, 'referat');
        }

        return [
            'items'         => $organizations,
            'firstPage'     => OParl10Controller::getOparlListUrl('organization', $body),
            'lastPage'      => OParl10Controller::getOparlListUrl('organization', $body),
            'numberOfPages' => 1,
        ];
    }

}
