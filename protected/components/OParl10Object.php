<?php

/**
 * Enthält die Funktionen zum Erzeugen alle OParl-Objekte. Der Zugriff auf die Objekte wird durch die get()-Methode
 * abstrahiert.
 */
class OParl10Object {
    const TYPE_AGENDAITEM      = 'https://schema.oparl.org/1.0/AgendaItem';
    const TYPE_BODY            = 'https://schema.oparl.org/1.0/Body';
    const TYPE_CONSULTATION    = 'https://schema.oparl.org/1.0/Consultation';
    const TYPE_FILE            = 'https://schema.oparl.org/1.0/File';
    const TYPE_LEGISLATIVETERM = 'https://schema.oparl.org/1.0/LegislativeTerm';
    const TYPE_LOCATION        = 'https://schema.oparl.org/1.0/Location';
    const TYPE_MEETING         = 'https://schema.oparl.org/1.0/Meeting';
    const TYPE_MEMBERSHIP      = 'https://schema.oparl.org/1.0/Membership';
    const TYPE_ORGANIZATION    = 'https://schema.oparl.org/1.0/Organization';
    const TYPE_PAPER           = 'https://schema.oparl.org/1.0/Paper';
    const TYPE_PERSON          = 'https://schema.oparl.org/1.0/Person';
    const TYPE_SYSTEM          = 'https://schema.oparl.org/1.0/System';

    /**
     * Amtlicher Gemeindeschlüssel für München
     *
     * Quelle: http://www.statistik-portal.de/Statistik-Portal/gemeindeverz.asp?G=M%FCnchen
     */
    const AGS_MUENCHEN = "09162000";

    /**
     * Regionalschlüssel für München
     *
     * Quelle: https://www.google.de/url?sa=t&rct=j&q=&esrc=s&source=web&cd=3&ved=0ahUKEwjpz_TP_IPTAhXBiiwKHb2fCfEQF \
     * gg1MAI&url=https%3A%2F%2Fwww.destatis.de%2FDE%2FZahlenFakten%2FLaenderRegionen%2FRegionales%2FGemeindeverzeich \
     * nis%2FAdministrativ%2FArchiv%2FGVAuszugQ%2FAuszugGV3QAktuell.xls%3F__blob%3DpublicationFile&usg=AFQjCNFvzspzR \
     * iU3IfZ3gKFZPUF-rDWDgg
     */
    const RGS_MUENCHEN = "606109162000";

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
        else if ($type == 'agendaitem'     ) return self::agendaitem($id);
        else if ($type == 'consultation'   ) return self::consultation($id);
        else {
            header('HTTP/1.0 400 Bad Request');
            return ['error' => 'No such object type ' . $type];
        }
    }

    private static function agendaitem($id) {
        $item = Tagesordnungspunkt::model()->findByPk($id);

        $data = [
            'id' => OParl10Controller::getOparlObjectUrl('agendaitem', $item->id),
            'type' => self::TYPE_AGENDAITEM,
            "number" => $item->top_nr,
            "name" => $item->top_betreff,
            "consultation" => OParl10Controller::getOparlObjectUrl('consultation', $item->id),
            "resolutionText" => $item->beschluss_text,
        ];

        if ($item->entscheidung)
            $data["result"] = $item->entscheidung;


        return $data;
    }

    /**
     * Erzeugt ein 'oparl:Body'-Objekt, also den Stadtrat oder die Bezirksauschüsse
     */
    private static function body($id) {
        /** @var Bezirksausschuss $ba */
        $ba = Bezirksausschuss::model()->findByPk($id);

        if ($id == 0) {
            $name = 'Stadrat der Landeshauptstadt München';
            $shortName = 'Stadtrat';
            $location = null;
        } else {
            $name = 'Bezirksausschuss ' . $ba->ba_nr . ': ' . $ba->name;
            $shortName = 'BA ' . $ba->ba_nr;
            $location = self::location($id, 'body');
        }

        $data = [
            'id'              => OParl10Controller::getOparlObjectUrl('body', $ba->ba_nr),
            'type'            => self::TYPE_BODY,
            'system'          => OParl10Controller::getOparlObjectUrl('system', null),
            'contactEmail'    => Yii::app()->params['adminEmail'],
            'contactName'     => Yii::app()->params['adminEmailName'],
            'name'            => $name,
            'shortName'       => $shortName,
            'website'         => $ba->website,
            'legislativeTerm' => self::legislativeterm(-1),
            'organization'    => OParl10Controller::getOparlListUrl('organization', null, $ba->ba_nr),
            'person'          => OParl10Controller::getOparlListUrl('person', null, $ba->ba_nr),
            'meeting'         => OParl10Controller::getOparlListUrl('meeting', null, $ba->ba_nr),
            'paper'           => OParl10Controller::getOparlListUrl('paper', null, $ba->ba_nr),
            'web'             => SITE_BASE_URL . $ba->getLink(),
            'created'         => OParl10Controller::mysqlToOparlDateTime($ba->created),
            'modified'        => OParl10Controller::mysqlToOparlDateTime($ba->modified),
        ];

        if ($id == 0) {
            $data['ags'] = self::AGS_MUENCHEN;
            $data['rgs'] = self::RGS_MUENCHEN;
        }

        if ($location)
            $data['location'] = $location;

        return $data;
    }

    private static function consultation($id) {
        $item = Tagesordnungspunkt::model()->findByPk($id);

        $data = [
            "id" => OParl10Controller::getOparlObjectUrl('consultation', $item->id),
            "type" => self::TYPE_CONSULTATION,
            "meeting" => OParl10Controller::getOparlObjectUrl('meeting', $item->sitzungstermin_id),
            "organization" => [
                OParl10Controller::getOparlObjectUrl('organization', $item->gremium_id, 'gremium')
            ],
        ];

        if ($item->antrag_id)
            $data["paper"] = OParl10Controller::getOparlObjectUrl('paper', $item->antrag_id);

        return $data;
    }

    private static function location($id, $subtype) {
        if ($subtype != 'body') {
            header('HTTP/1.0 400 Bad Request');
            return ['error' => 'No such subtype ' . $subtype . ' for location. Only "body" is allowed.'];
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
            'id'                               => OParl10Controller::getOparlObjectUrl('file', $dokument->id),
            'type'                             => self::TYPE_FILE,
            'name'                             => $dokument->getName(),
            'muenchenTransparent:nameRaw'      => $dokument->name,
            'muenchenTransparent:nameTitelTaw' => $dokument->name_title,
            'accessUrl'                        => OPARL_10_ROOT . '/fileaccess/access/' . $dokument->id,
            'downloadUrl'                      => OPARL_10_ROOT . '/fileaccess/download/' . $dokument->id,
            'fileName'                         => $dokument->getDateiname(),
            'web'                              => SITE_BASE_URL . $dokument->getLink(),
            'created'                          => OParl10Controller::mysqlToOparlDateTime($dokument->created),
            'modified'                         => OParl10Controller::mysqlToOparlDateTime($dokument->modified),
        ];

        if (substr($dokument->url, -strlen('.pdf')) === '.pdf') {
            $data['mimeType' ] = 'application/pdf';
        } else if (substr($dokument->url, -strlen('.tiff')) === '.tiff' || substr($dokument->url, -strlen('.tif')) === '.tif') {
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
            'meetingState' => $termin->sitzungsstand,
            'start'        => OParl10Controller::mysqlToOparlDateTime($termin->termin),
            'web'          => SITE_BASE_URL . $termin->getLink(),
            'created'      => OParl10Controller::mysqlToOparlDateTime($termin->created),
            'modified'     => OParl10Controller::mysqlToOparlDateTime($termin->modified),
        ];

        // Inkonsitenzen im Datenmodell abfangen
        if ($termin->gremium != null) {
            $data['name'] = $termin->gremium->name;
            $data['organization'] = [OParl10Controller::getOparlObjectUrl('organization', $termin->gremium->id, 'gremium')];
        }

        $data['auxiliaryFile'] = [];
        foreach ($termin->antraegeDokumente as $dokument)
            $data['auxiliaryFile'][] = self::file($dokument->id);

        $data['agendaItem'] = [];
        foreach ($termin->tagesordnungspunkte as $top)
            $data['agendaItem'][] = self::agendaitem($top->id);

        if ($termin->abgesetzt)
            $data['cancelled'] = true;
        return $data;
    }

    /**
     * Erzeugt ein 'oparl:Membership'-Objekt, das die Mitgliedschaften eines Stadrats in einer Fraktion, einem Gremium, Referat abbildet
     */
    private static function membership($id, $subtype) {
        if ($subtype == 'fraktion') {
            $object = StadtraetInGremium::model()->findByPk($id);
            $role = $object->funktion;
        } else if ($subtype == 'gremium') {
            $object = StadtraetInGremium::model()->findByPk($id);
            $role = $object->funktion;
        } else if ($subtype == 'referat') {
            $object = StadtraetInReferat::model()->findByPk($id);
            $role = $object->getFunktion();
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
            'created'      => OParl10Controller::mysqlToOparlDateTime($object->created),
            'modified'     => OParl10Controller::mysqlToOparlDateTime($object->modified),
        ];

        if ($role !== null)
            $data['role'] = $role;

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
            $object = Gremium::model()->findByPk($id);
            $memberships = $object->mitgliedschaften;
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
        // FIXME: Externe Liste mit Meetings
        /*if ($subtype == 'gremium') {
            $data['meeting'] = [];
            foreach ($object->termine as $termin) {
                $data['meeting'][] = OParl10Controller::getOparlObjectUrl('meeting', $termin->id);
            }
        }*/

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
            'id'                     => OParl10Controller::getOparlObjectUrl('paper', $antrag->id),
            'type'                   => self::TYPE_PAPER,
            'body'                   => OParl10Controller::getOparlObjectUrl('body', ($antrag->ba_nr != null ? $antrag->ba_nr : 0)),
            'name'                   => $antrag->getName(),
            'reference'              => $antrag->antrags_nr,
            'paperType'              => $antrag->getTypName(),
            'auxiliaryFile'          => [],
            'originatorPerson'       => [],
            'originatorOrganization' => [],
            'underDirectionOf'       => [OParl10Controller::getOparlObjectUrl('organization', $antrag->referat_id, 'referat')],
            'keyword'                => [],
            'web'                    => SITE_BASE_URL . $antrag->getLink(),
            'created'                => OParl10Controller::mysqlToOparlDateTime($antrag->created),
            'modified'               => OParl10Controller::mysqlToOparlDateTime($antrag->modified),
        ];

        foreach ($antrag->antraegePersonen as $ap) {
            if ($ap->typ == AntragPerson::$TYP_GESTELLT_VON) {
                $organization = Gremium::model()->findByName($ap->person->name);
                if ($organization) {
                    $data['originatorOrganization'][] = OParl10Controller::getOparlObjectUrl('organization', $organization->id)
                }
            } else if ($ap->typ == AntragPerson::$TYP_INITIATORIN) {
                $data['originatorPerson'][] = OParl10Controller::getOparlObjectUrl('person', $ap->person->id)
            }
        }

        foreach ($antrag->dokumente as $dokument)
            $data['auxiliaryFile'][] = self::file($dokument->id);

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

        $memberships = array_merge(
            $stadtraetin->getMembershipsByType(Gremium::TYPE_STR_FRAKTION),
            $stadtraetin->getMembershipsByType(Gremium::TYPE_BA_FRAKTION),
        );
        if (count($memberships) > 0) {
            $body = $memberships[0]->gremium->ba_nr;
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
            $data['status'] = ['Berufsmäßiger Stadtrat'];
        else
            $data['status'] = ['Ehrenamtlicher Stadtrat'];

        if ($stadtraetin->email != '')
            $data['email'] = [$stadtraetin->email];

        // optionale Attribute
        $optional_properties = [
            'life'                                  => $stadtraetin->beschreibung,
            'lifeSource'                            => $stadtraetin->quellen,
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
            'body'               => OParl10Controller::getOparlListUrl('body', null, null),
            'name'               => Yii::app()->params['projectTitle'],
            'contactEmail'       => Yii::app()->params['adminEmail'],
            'contactName'        => Yii::app()->params['adminEmailName'],
            'website'            => SITE_BASE_URL,
            'vendor'             => 'https://github.com/codeformunich/Muenchen-Transparent',
            'product'            => Yii::app()->createAbsoluteUrl('/infos/api'),
            'web'                => SITE_BASE_URL,
        ];
    }
}
