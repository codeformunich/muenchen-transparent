<?php

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

    /*
     * Gibt ein belibiges Objekt als array zurück
     */
    public static function object($typ, $id = null) {
        if      ($typ == 'system'                ) return self::system();
        else if ($typ == 'person'                ) return self::person($id);
        else if ($typ == 'file'                  ) return self::file($id);
        else if ($typ == 'organization_fraktion' ) return self::organization_fraktion($id);
        else if ($typ == 'organization_gremium'  ) return self::organization_gremium($id);
        else if ($typ == 'organization_referat'  ) return self::organization_referat($id);
        else if ($typ == 'membership_fraktion'   ) return self::membership_fraktion($id);
        else if ($typ == 'membership_gremium'    ) return self::membership_gremium($id);
        else if ($typ == 'membership_referat'    ) return self::membership_referat($id);
        else if ($typ == 'term'                  ) return self::terms($id);
        else if ($typ == 'body'                  ) {
            // FIXME: https://github.com/codeformunich/Muenchen-Transparent/issues/135
            if ($id == 0) {
                $body = 0;
                $name = 'Stadrat der Landeshauptstadt München';
                $shortName = 'Stadtrat';
                $website = 'http://www.muenchen.de/';
            } else {
                $ba = Bezirksausschuss::model()->findByPk($id);
                $body = $ba->ba_nr;
                $name = 'Bezirksausschuss ' . $ba->ba_nr . ': ' . $ba->name;
                $shortName = 'BA ' . $ba->ba_nr;
                $website = Yii::app()->createAbsoluteUrl($ba->getLink());
            }
            return OParl10Object::body($body, $name, $shortName, $website);
        } else return ['error' => 'Object of typ ' . $typ . ' (and id=' . $id . ') not found.'];
    }

    /**
     * Erzeugt das 'oparl:System'-Objekt, also den API-Einstiegspunkt
     */
    public static function system() {
        return [
            'id'                 => OParl10Controller::getOparlObjectUrl('system'),
            'type'               => self::TYPE_SYSTEM,
            'oparlVersion'       => OParl10Controller::VERSION,
            'otherOparlVersions' => [],
            'body'               => OParl10Controller::getOparlListUrl('body'),
            'name'               => Yii::app()->params['projectTitle'],
            'contactEmail'       => Yii::app()->params['adminEmail'],
            'contactName'        => Yii::app()->params['adminEmailName'],
            'website'            => SITE_BASE_URL,
            'vendor'             => 'https://github.com/codeformunich/Muenchen-Transparent',
            'product'            => 'https://github.com/codeformunich/Muenchen-Transparent',
        ];
    }

    /**
     * Erzeugt ein 'oparl:Body'-Objekt, also den Stadtrat oder die Bezirksauschüsse
     */
    public static function body($body, $name, $shortName, $website) {
        return [
            'id'              => OParl10Controller::getOparlObjectUrl('body', $body),
            'type'            => self::TYPE_BODY,
            'system'          => OParl10Controller::getOparlObjectUrl('system'),
            'contactEmail'    => Yii::app()->params['adminEmail'],
            'contactName'     => Yii::app()->params['adminEmailName'],
            'name'            => $name,
            'shortName'       => $shortName,
            'website'         => $website,
            'organization'    => OParl10Controller::getOparlListUrl('organization', $body),
            'person'          => OParl10Controller::getOparlListUrl('person',       $body),
            'meeting'         => OParl10Controller::getOparlListUrl('meeting',      $body),
            'paper'           => OParl10Controller::getOparlListUrl('paper',        $body),
            'terms'           => OParl10Controller::getOparlListUrl('term',         $body),
        ];
    }

    /**
     * Erzeugt die statische Liste mit allen 'oparl:LegislativeTerm'-Objekten, also den Legislaturperioden
     *
     * Wenn als id -1 übergeben wird, dann wird die gesammte Liste zurückgegeben
     */
    public static function terms($id) {
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

        if ($id == -1)
            return $data;
        else
            return $data[$id];
    }

    /**
     * Erzeugt ein 'oparl:Organization'-Objekt, das ein Germium abbildet
     */
    public static function organization_gremium($id) {
        $object      = Gremium::model()->findByPk($id);
        $meetings    = [];
        $memberships = [];

        return [
            'id'             => OParl10Controller::getOparlObjectUrl('organization_gremium', $object->id),
            'type'           => self::TYPE_ORGANIZATION,
            'body'           => OParl10Controller::getOparlObjectUrl('body', $object->ba_nr == null ? 0 : $object->ba_nr),
            'name'           => $object->getName(false),
            'shortName'      => $object->getName(true),
            'meeting'        => $meetings,
            'membership'     => $memberships,
            'classification' => $object->getTypName(),
        ];
    }

    /**
     * Erzeugt ein 'oparl:Organization'-Objekt, das ein Fraktion abbildet
     */
    public static function organization_fraktion($id) {
        $object      = Fraktion::model()->findByPk($id);
        $meetings    = [];
        $memberships = [];

        return [
            'id'             => OParl10Controller::getOparlObjectUrl('organization_fraktion', $object->id),
            'type'           => self::TYPE_ORGANIZATION,
            'body'           => OParl10Controller::getOparlObjectUrl('body', $object->ba_nr == null ? 0 : $object->ba_nr),
            'name'           => $object->getName(false),
            'shortName'      => $object->getName(true),
            'meeting'        => $meetings,
            'membership'     => $memberships,
            'classification' => $object->getTypName(),
        ];
    }

    /**
     * Erzeugt ein 'oparl:Organization'-Objekt, das ein Referat abbildet
     */
    public static function organization_referat($id) {
        $object      = Referat::model()->findByPk($id);
        $meetings    = [];
        $memberships = [];

        return [
            'id'             => OParl10Controller::getOparlObjectUrl('organization_referat', $object->id),
            'type'           => self::TYPE_ORGANIZATION,
            'body'           => OParl10Controller::getOparlObjectUrl('body', 0),
            'name'           => $object->getName(false),
            'shortName'      => $object->getName(true),
            'meeting'        => $meetings,
            'membership'     => $memberships,
            'classification' => $object->getTypName(),
        ];
    }

    /**
     * Erzeugt ein 'oparl:Person'-Objekt, das StadträtInnen abbildet
     */
    public static function person($id) {
        $stadtraetin = StadtraetIn::model()->findByPk($id);

        $body = 0; // fallback

        if (count($stadtraetin->getFraktionsMitgliedschaften()) > 0) {
            $body = $stadtraetin->getFraktionsMitgliedschaften()[0]->fraktion->ba_nr;
            if ($body == null)
                $body = 0;
        }

        // Zwingende Attribute
        $data = [
            'id'   => OParl10Controller::getOparlObjectUrl('person', $stadtraetin->id),
            'type' => self::TYPE_PERSON,
            'body' => OParl10Controller::getOparlObjectUrl('body', $body),
            'name' => $stadtraetin->name,
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
            $data['status'] = "Berufsmäßiger Stadtrat";
        else
            $data['status'] = "Ehrenamtlicher Stadtrat";

        // optionale Attribute
        $optional_properties = [
            'life'                                   => $stadtraetin->beschreibung,
            'lifeSource'                             => $stadtraetin->quellen,
            'email'                                  => $stadtraetin->email,
            'muenchen-transparent:elected'           => $stadtraetin->gewaehlt_am,
            'muenchen-transparent:dateOfBirth'       => $stadtraetin->geburtstag,
            'muenchen-transparent:beruf'             => $stadtraetin->beruf,
            'muenchen-transparent:bio'               => $stadtraetin->bio,
            'muenchen-transparent:website'           => $stadtraetin->web,
            'muenchen-transparent:twitter'           => $stadtraetin->twitter,
            'muenchen-transparent:facebook'          => $stadtraetin->facebook,
            'muenchen-transparent:abgeordnetenwatch' => $stadtraetin->abgeordnetenwatch,
        ];

        foreach ($optional_properties as $key => $value) {
            if ($value && $value != "")
                $data[$key] = $value;
        }

        return $data;
    }

    /**
     * Erzeugt ein 'oparl:File'-Objekt, das Dokumente abbildet
     */
    public static function file($id) {
        $dokument = Dokument::model()->findByPk($id);

        $data = [
            'id'        => OParl10Controller::getOparlObjectUrl('file', $dokument->id),
            'type'      => self::TYPE_FILE,
            'fileName'  => $dokument->getName(true) . '.pdf',
            'name'      => $dokument->getName(),
            'mimeType'  => 'application/pdf', // FIXME: Es gibt auch tiff's! vgl. https://github.com/codeformunich/Muenchen-Transparent/issues/137
            'accessUrl' => $dokument->getLinkZumDokument() . '.pdf',
        ];

        if ($dokument->deleted)
            $data['delted'] = true;

        return $data;
    }

    /**
     * Erzeugt ein 'oparl:Membership'-Objekt, das die Mitgliedschaften eines Stadrats in einer Fraktion abbildet
     */
    public static function membership_fraktion($id) {
        $mitgliedschaft = StadtraetInFraktion::model()->findByPk($id);

        $data = [
            'id'                        => OParl10Controller::getOparlObjectUrl('membership_fraktion', $mitgliedschaft->id),
            'type'                      => self::TYPE_MEMBERSHIP,
            'organization'              => OParl10Controller::getOparlObjectUrl('organization_fraktion', $mitgliedschaft->fraktion->id),
            'person'                    => OParl10Controller::getOparlObjectUrl('person',  $mitgliedschaft->stadtraetIn->id),
            'role'                      => $mitgliedschaft->funktion,
            'startDate'                 => $mitgliedschaft->datum_von,
            'votingRight'               => true,
            'muenchen-transparent:term' => OParl10Controller::getOparlObjectUrl('term', $mitgliedschaft->wahlperiode),
        ];

        if ($mitgliedschaft->datum_bis !== null)
            $data['endDate'] = $mitgliedschaft->datum_bis;

        return $data;
    }

    /**
     * Erzeugt ein 'oparl:Membership'-Objekt, das den Bezug eines Berufsmäßigen Stadrats zu seinem Referat abbildet
     */
    public static function membership_referat($id) {
        $mitgliedschaft = StadtraetInReferat::model()->findByPk($id);

        $data = [
            'id'                        => OParl10Controller::getOparlObjectUrl('membership_referat', $mitgliedschaft->id),
            'type'                      => self::TYPE_MEMBERSHIP,
            'organization'              => OParl10Controller::getOparlObjectUrl('organization_referat', $mitgliedschaft->referat->id),
            'person'                    => OParl10Controller::getOparlObjectUrl('person',  $mitgliedschaft->stadtraetIn->id),
            'role'                      => 'Referent',
        ];

        if ($mitgliedschaft->datum_von !== null)
            $data['startDate'] = $mitgliedschaft->datum_von;

        if ($mitgliedschaft->datum_bis !== null)
            $data['endDate'] = $mitgliedschaft->datum_bis;

        return $data;
    }

}
