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
    public static function get($type, $body, $id = null, $created_since = null, $created_until = null, $modified_since = null, $modified_until = null) {
        $criteria = self::criteria($created_since, $created_until, $modified_since, $modified_until);

        if      ($type == 'body'         ) return self::body();
        else if ($type == 'organization' ) return self::organization($body, $criteria);
        else if ($type == 'person'       ) return self::externalList($body, $criteria, $type, $id);
        else if ($type == 'meeting'      ) return self::externalList($body, $criteria, $type, $id);
        else if ($type == 'paper'        ) return self::externalList($body, $criteria, $type, $id);
        else {
            header('HTTP/1.0 400 Bad Request');
            return ['error' => 'No external list for type ' . $type];
        }
    }

    /**
     * Erzeugt ein CDbCriteria-Objekt mit den Filtern für created und modified
     */
    public static function criteria($created_since, $created_until, $modified_since, $modified_until) {
        $criteria = new CDbCriteria();
        if ($created_since  !== null) {
            $criteria->addCondition('created  >= :created_since');
            $criteria->params["created_since"] = $created_since;
        }
        if ($created_until  !== null) {
            $criteria->addCondition('created  <= :created_until');
            $criteria->params["created_until"] = $created_until;
        }
        if ($modified_since !== null) {
            $criteria->addCondition('modified >= :modified_since');
            $criteria->params["modified_since"] = $modified_since;
        }
        if ($modified_until !== null) {
            $criteria->addCondition('modified <= :modified_until');
            $criteria->params["modified_until"] = $modified_until;
        }
        return $criteria;
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
            'data'       => $bodies,
            'pagination' => [
                'totalPages'   => 1,
                'currentPages' => 1,
            ],
            'links'      => [
                'first'        => OParl10Controller::getOparlListUrl('body'),
                'last'         => OParl10Controller::getOparlListUrl('body'),            ]
        ];
    }

    /**
     * Eine allgemeine externe Objektliste, die für verschiedene Objekte genutzt werden kann.
     * In Moment sind das:
     *  - person
     *  - meeting
     *  - paper
     */
    private static function externalList($body, $criteria, $type, $id)
    {
        $ba_check = true;

        if        ($type == 'person'  ) {
            $model = StadtraetIn::model();
            $ba_check = false;
        } else if ($type == 'meeting' ) {
            $model = Termin::model();
        } else if ($type == 'paper'   ) {
            $model = Antrag::model();
        }

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
            'data'       => $oparl_entries,
            'pagination' => [
                'elementsPerPage' => static::ITEMS_PER_PAGE,
                'totalPages'      => ceil($count / static::ITEMS_PER_PAGE),
            ],
            'links'      => [
                'first'           => OParl10Controller::getOparlListUrl($type, $body),
            ]
        ];

        if (count($entries) > 0 && end($entries)->id != $last_entry->id)
            $data['links']['next'] = OParl10Controller::getOparlListUrl($type, $body, end($entries)->id);

        return $data;
    }

    /**
     * Die externe Objektliste mit allen 'oparl:Organization'-Objekten, d.h.
     * - die Gremien
     * - die Frakionen
     * - nur beim Stadtrat: die Referate
     */
    private static function organization($body, $criteria)
    {
        $organizations = [];

        if ($body == 0) {
            $referate = Referat::model()->findAll($criteria);
            foreach ($referate as $referat)
                $organizations[] = OParl10Object::get('organization', $referat->id, 'referat');
        }

        if ($body == 0) {
            $criteria->addCondition('ba_nr IS NULL');
        } else {
            $criteria->addCondition('ba_nr = :body');
            $criteria->params['body'] = $body;
        }

        $gremien = Gremium::model()->findAll($criteria);
        foreach ($gremien as $gremium)
            $organizations[] = OParl10Object::get('organization', $gremium->id, 'gremium');

        $fraktionen = Fraktion::model()->findAll($criteria);
        foreach ($fraktionen as $fraktion)
            $organizations[] = OParl10Object::get('organization', $fraktion->id, 'fraktion');

        return [
            'data'         => $organizations,
            'pagination'    => [
                'totalPages'  => 1,
                'currentPage' => 1,
            ],
            'links'        => [
                'firstPage'   => OParl10Controller::getOparlListUrl('organization', $body),
                'lastPage'    => OParl10Controller::getOparlListUrl('organization', $body),
            ]
        ];
    }

}
