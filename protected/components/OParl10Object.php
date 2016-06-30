<?php

/**
 * Enthält die Funktionen zum Erzeugen alle OParl-Objekte. Der Zugriff auf die Objekte wird durch die get()-Methode
 * abstrahiert.
 */
class OParl10Object {
    const TYPE_AGENDAITEM      = 'https://oparl.org/schema/1.0/AgendaItem';
    const TYPE_BODY            = 'https://oparl.org/schema/1.0/Body';
    const TYPE_CONSULTATION    = 'https://oparl.org/schema/1.0/Consultation';
    const TYPE_FILE            = 'https://oparl.org/schema/1.0/File';
    const TYPE_LEGISLATIVETERM = 'https://oparl.org/schema/1.0/LegislativeTerm';
    const TYPE_LOCATION        = 'https://oparl.org/schema/1.0/Location';
    const TYPE_MEETING         = 'https://oparl.org/schema/1.0/Meeting';
    const TYPE_MEMBERSHIP      = 'https://oparl.org/schema/1.0/Membership';
    const TYPE_ORGANIZATION    = 'https://oparl.org/schema/1.0/Organization';
    const TYPE_PAPER           = 'https://oparl.org/schema/1.0/Paper';
    const TYPE_PERSON          = 'https://oparl.org/schema/1.0/Person';
    const TYPE_SYSTEM          = 'https://oparl.org/schema/1.0/System';

    /**
     * Gibt ein beliebiges OParl-Objekt im Form eines arrays zurück
     */
    public static function get($type, $id, $subtype = null) {
        if      ($type == 'body'           ) return self::body($id);
        else if ($type == 'file'           ) return self::file($id);
        else if ($type == 'legislativeterm') return self::legislativeterm($id);
        else if ($type == 'meeting'        ) return self::meeting($id);
        else if ($type == 'membership'     ) return self::membership($id, $subtype);
        else if ($type == 'organization'   ) return self::organization($id, $subtype);
        else if ($type == 'paper'          ) return self::paper($id);
        else if ($type == 'person'         ) return self::person($id);
        else if ($type == 'system'         ) return self::system();
        else if ($type == 'location'       ) return self::location($id, $subtype);
        else if ($type == 'agendaitem'     ) return ['note:' => 'not implemented yet'];
        else if ($type == 'consultation'   ) return ['note:' => 'not implemented yet'];
        else {
            header('HTTP/1.0 400 Bad Request');
            return ['error' => 'No such object type ' . $type];
        }
    }

    /**
     * Erzeugt ein 'oparl:Body'-Objekt, also den Stadtrat oder die Bezirksauschüsse
     */
    private static function body($id) {
        // FIXME: https://github.com/codeformunich/Muenchen-Transparent/issues/135
        if ($id == 0) {
            $body = 0;
            $name = 'Stadrat der Landeshauptstadt München';
            $shortName = 'Stadtrat';
            $website = 'http://www.muenchen.de/';
            $location = null;
            $web = SITE_BASE_URL;
        } else {
            /** @var Bezirksausschuss $ba */
            $ba = Bezirksausschuss::model()->findByPk($id);
            $body = $ba->ba_nr;
            $name = 'Bezirksausschuss ' . $ba->ba_nr . ': ' . $ba->name;
            $shortName = 'BA ' . $ba->ba_nr;
            $website = $ba->website;
            $location = self::location($id, 'body');
            $web = SITE_BASE_URL . $ba->getLink();
        }

        $data = [
            'id'              => OParl10Controller::getOparlObjectUrl('body', $body),
            'type'            => self::TYPE_BODY,
            'system'          => OParl10Controller::getOparlObjectUrl('system', null),
            'contactEmail'    => Yii::app()->params['adminEmail'],
            'contactName'     => Yii::app()->params['adminEmailName'],
            'name'            => $name,
            'shortName'       => $shortName,
            'website'         => $website,
            'organization'    => OParl10Controller::getOparlListUrl('organization', $body),
            'person'          => OParl10Controller::getOparlListUrl('person',       $body),
            'meeting'         => OParl10Controller::getOparlListUrl('meeting',      $body),
            'paper'           => OParl10Controller::getOparlListUrl('paper',        $body),
            'legislativeTerm' => self::legislativeterm(-1),
            'web'             => $web,
        ];

        if ($location)
            $data['location'] = $location;

        return $data;
    }

    private static function location($id, $subtype) {
        if ($subtype != 'body') {
            header('HTTP/1.0 400 Bad Request');
            return ['error' => 'No such subtype ' . $subtype . ' for location'];
        }

        /** @var Bezirksausschuss $ba */
        $ba = Bezirksausschuss::model()->findByPk($id);

        return [
            'id'      => OParl10Controller::getOparlObjectUrl('location', $id, 'body'),
            'type'    => self::TYPE_LOCATION,
            'bodies'  => [OParl10Controller::getOparlObjectUrl('body', $id)],
            'geojson' => $ba->toGeoJSONArray(),
        ];
    }

    /**
     * Erzeugt ein 'oparl:LegislativeTerm'-Objekten, also eine Legislaturperiode
     *
     * Wenn als id -1 übergeben wird, dann wird die gesammte Liste zurückgegeben
     */
    private static function legislativeterm($id) {
        $data = [
            [
                'type'      => self::TYPE_LEGISLATIVETERM,
                'name'      => 'Unbekannt',
                'startDate' => '0000-00-00',
                'endDate'   => '0000-00-00',
            ],
            [
                'type'      => self::TYPE_LEGISLATIVETERM,
                'name'      => '1996-2002',
                'startDate' => '1996-12-03',
                'endDate'   => '2002-12-03',
            ],
            [
                'type'      => self::TYPE_LEGISLATIVETERM,
                'name'      => '2002-2008',
                'startDate' => '2002-12-03',
                'endDate'   => '2008-12-03',
            ],
            [
                'type'      => self::TYPE_LEGISLATIVETERM,
                'name'      => '2008-2014',
                'startDate' => '2008-12-03',
                'endDate'   => '2014-12-03',
            ],
            [
                'type'      => self::TYPE_LEGISLATIVETERM,
                'name'      => '2014-2020',
                'startDate' => '2014-12-03',
                'endDate'   => '2020-12-03',
            ],
        ];

        // id's setzen
        foreach ($data as $i => &$val) {
            $val['id'] = OParl10Controller::getOparlObjectUrl('legislativeterm', $i);
        }

        if ($id == -1)
            return $data;
        else
            return $data[$id];
    }

    /**
     * Erzeugt ein 'oparl:File'-Objekt, das ein Dokument abbildet
     */
    private static function file($id) {
        /** @var Dokument $dokument */
        $dokument = Dokument::model()->findByPk($id);

        $data = [
            'id'          => OParl10Controller::getOparlObjectUrl('file', $dokument->id),
            'type'        => self::TYPE_FILE,
            'name'        => $dokument->getName(),
            'accessUrl'   => SITE_BASE_URL . '/fileaccess/access/' . $dokument->id,
            'downloadUrl' => SITE_BASE_URL . '/fileaccess/download/' . $dokument->id,
            'fileName'    => $dokument->getDateiname(),
            'web'         => SITE_BASE_URL . $dokument->getLink(),
            'created'     => OParl10Controller::mysqlToOparlDateTime($dokument->created),
            'modified'    => OParl10Controller::mysqlToOparlDateTime($dokument->modified),
        ];

        if (substr($dokument->url, -strlen('.pdf')) === '.pdf') {
            $data['mimeType' ] = 'application/pdf';
        } else if (substr($dokument->url, -strlen('.tiff')) === '.tiff') {
            $data['mimeType' ] = 'image/tiff';
        }

        if ($dokument->termin)
            $data['meeting'] = [OParl10Controller::getOparlObjectUrl('meeting', $dokument->termin->id)];

        if ($dokument->antrag)
            $data['paper'] = [OParl10Controller::getOparlObjectUrl('paper', $dokument->antrag->id)];

        // TODO
        /*
        if ($dokument->tagesordnungspunkt)
            $data['agendaItem'] = [OParl10Controller::getOparlObjectUrl('agendaItem', $dokument->tagesordnungspunkt->id)];
        */

        if ($dokument->ocr_von)
            $data['muenchenTransparent:ocrCreator'] = $dokument->ocr_von;

        if ($dokument->deleted)
            $data['deleted'] = true;

        return $data;
    }

    /**
     * Erzeugt ein 'oparl:Meeting'-Objekt, das einen Termin abbildet
     */
    private static function meeting($id) {
        /** @var Termin $termin */
        $termin = Termin::model()->findByPk($id);

        $data = [
            'id'           => OParl10Controller::getOparlObjectUrl('meeting', $termin->id),
            'type'         => self::TYPE_MEETING,
            'name'         => $termin->gremium->name,
            'meetingState' => $termin->sitzungsstand,
            'start'        => OParl10Controller::mysqlToOparlDateTime($termin->termin),
            'organization' => OParl10Controller::getOparlObjectUrl('organization', $termin->gremium->id, 'gremium'),
            'web'          => SITE_BASE_URL . $termin->getLink(),
            'created'      => OParl10Controller::mysqlToOparlDateTime($termin->created),
            'modified'     => OParl10Controller::mysqlToOparlDateTime($termin->modified),
        ];

        $data['auxiliaryFile'] = [];
        foreach ($termin->antraegeDokumente as $dokument)
            $data['auxiliaryFile'][] = OParl10Controller::getOparlObjectUrl('file', $dokument->id);

        if ($termin->abgesetzt)
            $data['cancelled'] = true;
        return $data;
    }

    /**
     * Erzeugt ein 'oparl:Membership'-Objekt, das die Mitgliedschaften eines Stadrats in einer Fraktion, einem Gremium, Referat abbildet
     */
    private static function membership($id, $subtype) {
        if ($subtype == 'fraktion') {
            $object = StadtraetInFraktion::model()->findByPk($id);
        } else if ($subtype == 'gremium') {
            $object = StadtraetInGremium::model()->findByPk($id);
        } else if ($subtype == 'referat') {
            $object = StadtraetInReferat::model()->findByPk($id);
        } else {
            header('HTTP/1.0 400 Bad Request');
            return ['error' => 'No such subtype ' . $subtype . ' for membership'];
        }
        $organization = $object->$subtype;

        $data = [
            'id'           => OParl10Controller::getOparlObjectUrl('membership', $object->id, $subtype),
            'type'         => self::TYPE_MEMBERSHIP,
            'organization' => OParl10Controller::getOparlObjectUrl('organization', $organization->id, $subtype),
            'person'       => OParl10Controller::getOparlObjectUrl('person', $object->stadtraetIn->id),
            'role'         => $object->getFunktion(),
            'created'      => OParl10Controller::mysqlToOparlDateTime($object->created),
            'modified'     => OParl10Controller::mysqlToOparlDateTime($object->modified),
        ];

        if ($object->datum_von !== null)
            $data['startDate'] = $object->datum_von;

        if ($object->datum_bis !== null)
            $data['endDate'] = $object->datum_bis;

        return $data;
    }

    /**
     * Erzeugt ein 'oparl:Organization'-Objekt, das ein Germium, eine Fraktion oder ein Referat abbildet
     */
    private static function organization($id, $subtype) {
        if ($subtype == 'fraktion') {
            $object = Fraktion::model()->findByPk($id);
            $memberships = $object->stadtraetInnenFraktionen;
        } else if ($subtype == 'gremium') {
            $object = Gremium::model()->findByPk($id);
            $memberships = $object->mitgliedschaften;
        } else if ($subtype == 'referat') {
            $object = Referat::model()->findByPk($id);
            $memberships = $object->stadtraetInnenReferate;
        } else {
            header('HTTP/1.0 400 Bad Request');
            return ['error' => 'No such subtype ' . $subtype . ' for organization'];
        }

        $data = [
            'id'             => OParl10Controller::getOparlObjectUrl('organization', $object->id, $subtype),
            'type'           => self::TYPE_ORGANIZATION,
            'body'           => OParl10Controller::getOparlObjectUrl('body', $object->getBaNr()),
            'name'           => $object->getName(false),
            'shortName'      => $object->getName(true),
            'membership'     => [],
            'classification' => $object->getTypName(),
            'web'            => SITE_BASE_URL . $object->getLink(),
            'created'        => OParl10Controller::mysqlToOparlDateTime($object->created),
            'modified'       => OParl10Controller::mysqlToOparlDateTime($object->modified),
        ];

        // Termine gibt es nur bei Gremien
        if ($subtype == 'gremium') {
            $data['meetings'] = [];
            foreach ($object->termine as $termin) {
                $data['meetings'][] = OParl10Controller::getOparlObjectUrl('meeting', $termin->id);
            }
        }

        // Mitgliedschaften
        foreach ($memberships as $membership) {
            $data['membership'][] = OParl10Controller::getOparlObjectUrl('membership', $membership->id, $subtype);
        }

        return $data;
    }

    /**
     * Erzeugt ein 'oparl:Paper'-Objekt, das ein pdf (oder in Ausnahmefällen ein tiff) abbildet
     */
    private static function paper($id) {
        /** @var Antrag $antrag */
        $antrag = Antrag::model()->findByPk($id);

        $data = [
            'id'               => OParl10Controller::getOparlObjectUrl('paper', $antrag->id),
            'type'             => self::TYPE_PAPER,
            'body'             => OParl10Controller::getOparlObjectUrl('body', ($antrag->ba_nr != null ? $antrag->ba_nr : 0)),
            'name'             => $antrag->getName(),
            'reference'        => $antrag->antrags_nr,
            'paperType'        => $antrag->getTypName(),
            'auxiliaryFile'    => [],
            'underDirectionof' => [OParl10Controller::getOparlObjectUrl('organization', $antrag->referat_id, 'referat')],
            'keyword'          => [],
            'web'              => SITE_BASE_URL . $antrag->getLink(),
            'created'          => OParl10Controller::mysqlToOparlDateTime($antrag->created),
            'modified'         => OParl10Controller::mysqlToOparlDateTime($antrag->modified),
        ];

        foreach ($antrag->dokumente as $dokument)
            $data['auxiliaryFile'][] = OParl10Controller::getOparlObjectUrl('file', $dokument->id);

        foreach ($antrag->tags as $tags)
            $data['keyword'][] = $tags->name;

        if ($antrag->vorgang != null) {
            $data['relatedPaper'] = [];
            foreach ($antrag->vorgang->antraege as $verwandt)
                $data['relatedPaper'][] = OParl10Controller::getOparlObjectUrl('paper', $verwandt->id);
        }

        return $data;
    }

    /**
     * Erzeugt ein 'oparl:Person'-Objekt, das StadträtInnen abbildet
     */
    private static function person($id) {
        /** @var StadtraetIn $stadtraetin */
        $stadtraetin = StadtraetIn::model()->findByPk($id);

        $body = 0; // fallback

        if (count($stadtraetin->getFraktionsMitgliedschaften()) > 0) {
            $body = $stadtraetin->getFraktionsMitgliedschaften()[0]->fraktion->ba_nr;
            if ($body == null)
                $body = 0;
        }

        // Zwingende Attribute
        $data = [
            'id'         => OParl10Controller::getOparlObjectUrl('person', $stadtraetin->id),
            'type'       => self::TYPE_PERSON,
            'body'       => OParl10Controller::getOparlObjectUrl('body', $body),
            'name'       => $stadtraetin->name,
            'familyName' => $stadtraetin->errateNachname(),
            'givenName'  => $stadtraetin->errateVorname(),
            'web'        => SITE_BASE_URL . $stadtraetin->getLink(),
            'created'    => OParl10Controller::mysqlToOparlDateTime($stadtraetin->created),
            'modified'   => OParl10Controller::mysqlToOparlDateTime($stadtraetin->modified),
        ];

        // Das Geschlecht übersetzen
        if ($stadtraetin->geschlecht) {
            if ($stadtraetin->geschlecht == 'weiblich')
                $data['gender'] = 'female';
            else if ($stadtraetin->geschlecht == 'maennlich')
                $data['gender'] = 'male';
            else
                $data['gender'] = 'other';
        }

        if ($stadtraetin->referentIn)
            $data['status'] = 'Berufsmäßiger Stadtrat';
        else
            $data['status'] = 'Ehrenamtlicher Stadtrat';

        // optionale Attribute
        $optional_properties = [
            'life'                                  => $stadtraetin->beschreibung,
            'lifeSource'                            => $stadtraetin->quellen,
            'email'                                 => $stadtraetin->email,
            'muenchenTransparent:elected'           => $stadtraetin->gewaehlt_am,
            'muenchenTransparent:dateOfBirth'       => $stadtraetin->geburtstag,
            'muenchenTransparent:beruf'             => $stadtraetin->beruf,
            'muenchenTransparent:bio'               => $stadtraetin->bio,
            'muenchenTransparent:website'           => $stadtraetin->web,
            'muenchenTransparent:twitter'           => $stadtraetin->twitter,
            'muenchenTransparent:facebook'          => $stadtraetin->facebook,
            'muenchenTransparent:abgeordnetenwatch' => $stadtraetin->abgeordnetenwatch,
        ];

        foreach ($optional_properties as $key => $value) {
            if ($value && $value != '')
                $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Erzeugt das 'oparl:System'-Objekt, also den API-Einstiegspunkt
     */
    private static function system() {
        return [
            'id'                 => OParl10Controller::getOparlObjectUrl('system', null),
            'type'               => self::TYPE_SYSTEM,
            'oparlVersion'       => OParl10Controller::VERSION,
            'otherOparlVersions' => [],
            'body'               => OParl10Controller::getOparlListUrl('body'),
            'name'               => Yii::app()->params['projectTitle'],
            'contactEmail'       => Yii::app()->params['adminEmail'],
            'contactName'        => Yii::app()->params['adminEmailName'],
            'website'            => SITE_BASE_URL,
            'vendor'             => 'https://github.com/codeformunich/Muenchen-Transparent',
            'product'            => Yii::app()->createUrl('/infos/api'),
            'web'                => SITE_BASE_URL,
        ];
    }
}
