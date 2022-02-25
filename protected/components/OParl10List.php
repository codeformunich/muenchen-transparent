<?php

/**
 * Kapselt die Funktionen zum Erzeugen externer OParl-Listen
 */
class OParl10List
{
    const ITEMS_PER_PAGE = OPARL_10_ITEMS_PER_PAGE;

    /**
     * Gibt eine beliebiges externe OParl-Objektliste als array zurück
     *
     * @param $type string
     * @param $body int
     * @param $filter OParl10Filter
     * @return array
     */
    public static function get($type, $body, $filter) {
        if      ($type == 'body'         ) return self::body($filter);
        else if ($type == 'organization' ) return self::organization($body, $filter);
        else if ($type == 'person'       ) return self::externalList($body, $filter, $type);
        else if ($type == 'meeting'      ) return self::externalList($body, $filter, $type);
        else if ($type == 'paper'        ) return self::externalList($body, $filter, $type);
        else {
            header('HTTP/1.0 400 Bad Request');
            return ['error' => 'No external list for type ' . $type];
        }
    }

    /**
     * Die externe Objektliste mit allen 'oparl:Body'-Objekten
     * @param $filter OParl10Filter
     * @return array
     */
    private static function body($filter)
    {
        $criteria = new CDbCriteria();
        $filter->add_mandatory_filter($criteria);
        $bodies = [];
        $bas = Bezirksausschuss::model()->findAll($criteria);
        foreach ($bas as $ba)
            $bodies[] = OParl10Object::get('body', $ba->ba_nr);

        return [
            'data'       => $bodies,
            'pagination' => [
                'totalPages'   => 1,
                'currentPages' => 1,
            ],
            'links'      => [
                'first'        => OParl10Controller::getOparlListUrl('body', $filter, null),
                'last'         => OParl10Controller::getOparlListUrl('body', $filter, null),
            ]
        ];
    }

    /**
     * Eine allgemeine externe Objektliste, die für verschiedene Objekte genutzt werden kann.
     * In Moment sind das:
     *  - person
     *  - meeting
     *  - paper
     *
     * @param $body int
     * @param $filter OParl10Filter
     * @param $type string
     * @return array
     */
    private static function externalList($body, $filter, $type)
    {
        $criteria = new CDbCriteria();
        $filter->add_mandatory_filter($criteria);

        $ba_check = true;
        if        ($type == 'person'  ) {
            $model = StadtraetIn::model();
            $ba_check = false;
        } else if ($type == 'meeting' ) {
            $model = Termin::model();
        } else if ($type == 'paper'   ) {
            $model = Antrag::model();
        } else {
            assert(false);
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

        // Inkonsistenz im Datenmodell abfangen
        if ($type == "meeting") {
            $criteria->addCondition('gremium_id IS NOT NULL');
        }

        $count = $model->count($criteria);

        // Stabile Paginierung: Nur eine bestimmte Anzahl an Elementen ausgeben, deren id größer als $id ist
        $filter->add_pagination_filter($criteria, self::ITEMS_PER_PAGE);

        $entries = $model->findAll($criteria);
        $oparl_entries = [];
        foreach ($entries as $entry)
            $oparl_entries[] = OParl10Object::get($type, $entry->id);

        $last_entry_criteria = new CDbCriteria($criteria);
        $last_entry_criteria->order = 'id DESC';
        $last_entry = $model->find($last_entry_criteria);

        $data = [
            'data'       => $oparl_entries,
            'pagination' => [
                'elementsPerPage' => static::ITEMS_PER_PAGE,
                'totalPages'      => ceil($count / static::ITEMS_PER_PAGE),
            ],
            'links'      => [
                'first'           => OParl10Controller::getOparlListUrl($type, $filter, $body),
            ]
        ];

        if (count($entries) > 0 && end($entries)->id != $last_entry->id) {
            $filter->id = end($entries)->id;
            $data['links']['next'] = OParl10Controller::getOparlListUrl($type, $filter, $body);
        }

        return $data;
    }

    /**
     * Die externe Objektliste mit allen 'oparl:Organization'-Objekten, d.h.
     * - die Gremien
     * - die Frakionen
     * - nur beim Stadtrat: die Referate
     *
     * @param $body int
     * @param $filter OParl10Filter
     * @return array
     */
    private static function organization($body, $filter)
    {
        $criteria = new CDbCriteria();
        $filter->add_mandatory_filter($criteria);

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

        return [
            'data'         => $organizations,
            'pagination'    => [
                'totalPages'  => 1,
                'currentPage' => 1,
            ],
            'links'        => [
                'firstPage'   => OParl10Controller::getOparlListUrl('organization', $filter, $body),
                'lastPage'    => OParl10Controller::getOparlListUrl('organization', $filter, $body),
            ]
        ];
    }

}
