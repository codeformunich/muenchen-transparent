<?php

class OParl10Object {
    const VERSION           = 'https://oparl.org/specs/1.0/';

    const TYPE_BODY         = 'https://oparl.org/schema/1.0/Body';
    const TYPE_ORGANIZATION = 'https://oparl.org/schema/1.0/Organization';
    const TYPE_PAPER        = 'https://oparl.org/schema/1.0/Paper';
    const TYPE_PERSON       = 'https://oparl.org/schema/1.0/Person';
    const TYPE_SYSTEM       = 'https://oparl.org/schema/1.0/System';
    
    public static function getOparlObjectUrl($route, $body = null, $id = null) {
        if ($body == null) $body = 0;
        
        if ($route == 'system') {
            return OPARL_10_ROOT;
        } else if ($route == 'body') {
            return OPARL_10_ROOT . '/body/' . $body;
        }

        $url = OPARL_10_ROOT . '/body/' . $body . '/' . $route . '/' . $id;

        return $url;
    }
    
    public static function getOparlListUrl($route, $body = null, $id = null) {
        $url = OPARL_10_ROOT;
        if ($body !== null) $url .= '/body/' . $body;
        $url .= '/' . $route;
        if ($id !== null) $url .= '/' . $id;

        return $url;
    }
    
    /**
     */
    public static function system() {
        return [
            'id'                 => static::getOparlObjectUrl('system'),
            'type'               => static::TYPE_SYSTEM,
            'oparlVersion'       => static::VERSION,
            'otherOparlVersions' => [],
            'body'               => static::getOparlListUrl('bodies'),
            'name'               => Yii::app()->params['projectTitle'],
            'contactEmail'       => Yii::app()->params['adminEmail'],
            'contactName'        => Yii::app()->params['adminEmailName'],
            'website'            => SITE_BASE_URL,
            'vendor'             => 'https://github.com/codeformunich/Muenchen-Transparent',
            'product'            => 'https://github.com/codeformunich/Muenchen-Transparent',
        ];
    }

    /**
     * @param int $id
     */
    public static function body($body, $name, $shortName, $website) {
        return [
            'id'              => static::getOparlObjectUrl('body', $body),
            'type'            => static::TYPE_BODY,
            'system'          => static::getOparlObjectUrl('system'),
            'contactEmail'    => Yii::app()->params['adminEmail'],
            'contactName'     => Yii::app()->params['adminEmailName'],
            'name'            => $name,
            'shortName'       => $shortName,
            'website'         => $website,
            'organization'    => static::getOparlListUrl('organizations', $body),
            'person'          => static::getOparlListUrl('persons',       $body),
            'meeting'         => static::getOparlListUrl('meetings',      $body),
            'paper'           => static::getOparlListUrl('papers',        $body),
            'terms'           => static::getOparlListUrl('terms',         $body),
        ];
    }
    
    /**
     * @param int $ba_nr
     *
     * @return array
     */
    public static function terms($body) {
        return [
            0 => [
                'id'        => static::getOparlObjectUrl('term', $body, 0),
                'body'      => static::getOparlObjectUrl('body', $body),
                'name'      => 'Unbekannt',
                'startDate' => '0000-00-00',
                'endDate'   => '0000-00-00',
            ],
            1 => [
                'id'        => static::getOparlObjectUrl('term', $body, 1),
                'body'      => static::getOparlObjectUrl('body', $body),
                'name'      => '1996-2002',
                'startDate' => '1996-12-03',
                'endDate'   => '2002-12-03',
            ],
            2 => [
                'id'        => static::getOparlObjectUrl('term', $body, 2),
                'body'      => static::getOparlObjectUrl('body', $body),
                'name'      => '2002-2008',
                'startDate' => '2002-12-03',
                'endDate'   => '2008-12-03',
            ],
            3 => [
                'id'        => static::getOparlObjectUrl('term', $body, 3),
                'body'      => static::getOparlObjectUrl('body', $body),
                'name'      => '2008-2014',
                'startDate' => '2008-12-03',
                'endDate'   => '2014-12-03',
            ],
            4 => [
                'id'        => static::getOparlObjectUrl('term', $body, 4),
                'body'      => static::getOparlObjectUrl('body', $body),
                'name'      => '2014-2020',
                'startDate' => '2014-12-03',
                'endDate'   => '2020-12-03',
            ],
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public static function gremium($id) {
        $gremium     = Gremium::model()->findByPk($id);
        $meetings    = [];
        $memberships = [];

        return [
            'id'             => static::getOparlObjectUrl('gremium', $gremium->ba_nr, $gremium->id),
            'type'           => static::TYPE_ORGANIZATION,
            'body'           => static::getOparlObjectUrl('body', $gremium->ba_nr),
            'name'           => $gremium->getName(false),
            'shortName'      => $gremium->getName(true),
            'meeting'        => $meetings,
            'membership'     => $memberships,
            'classification' => $gremium->gremientyp,
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public static function fraktion($id) {
        $fraktion    = Fraktion::model()->findByPk($id);
        $meetings    = [];
        $memberships = [];

        return [
            'id'             => static::getOparlObjectUrl('fraktion', $fraktion->ba_nr, $fraktion->id),
            'type'           => static::TYPE_ORGANIZATION,
            'body'           => static::getOparlObjectUrl('body', $fraktion->ba_nr),
            'name'           => $fraktion->getName(false),
            'shortName'      => $fraktion->getName(true),
            'meeting'        => $meetings,
            'membership'     => $memberships,
            'classification' => 'Fraktion',
        ];
    }

    public static function person($id, $body) {
        $stadtraetin = StadtraetIn::model()->findByPk($id);
        
        $data = [
            'id'   => static::getOparlObjectUrl('person', $body, $stadtraetin->id),
            'type' => static::TYPE_PERSON,
            'name' => $stadtraetin->name,
        ];
        
        if ($stadtraetin->geschlecht) {
            if ($stadtraetin->geschlecht == 'weiblich')
                $data['gender'] = 'female';
            else if ($stadtraetin->geschlecht == 'maennlich')
                $data['gender'] = 'male';
            else
                $data['gender'] = 'other';
        }
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
            if ($value && $value != "") {
                $data[$key] = $value;
            }
        }
        
        return $data;
    }
}
