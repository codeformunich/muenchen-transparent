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
    public static function get($type, $body, $id = null) {
        if      ($type == 'body'           ) return self::body();
        else if ($type == 'organization'   ) return self::organization($body);
        else if ($type == 'person'         ) return self::externalList($body, $type, StadtraetIn::model(), false, $id);
        else if ($type == 'meeting'        ) return self::externalList($body, $type, Termin::model(), true, $id);
        else if ($type == 'paper'          ) return self::externalList($body, $type, Antrag::model(), true, $id);
        else if ($type == 'legislativeterm') return self::legislativeTerm($body);
        else {
            header('HTTP/1.0 400 Bad Request');
            return ['error' => 'No external list for type ' . $type];
        }
    }

    /**
     * Die externe Objektliste mit allen 'oparl:Body'-Objekten
     */
    private static function body()
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
    private static function externalList($body, $type, $model, $ba_check, $id = null)
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
            $oparl_entries[] = OParl10Object::get($type, $entry->id);

        $last_entry = $model->find(['order' => 'id DESC']);

        $data = [
            'items'         => $oparl_entries,
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => OParl10Controller::getOparlListUrl($type, $body),
            'numberOfPages' => ceil($count / static::ITEMS_PER_PAGE),
        ];

        if (count($entries) > 0 && end($entries)->id != $last_entry->id)
            $data['nextPage'] = OParl10Controller::getOparlListUrl($type, $body, end($entries)->id);

        return $data;
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
