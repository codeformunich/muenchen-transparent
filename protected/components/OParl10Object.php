<?php

class OParl10Object {
    const VERSION           = 'https://oparl.org/specs/1.0/';

    const TYPE_SYSTEM       = 'https://oparl.org/schema/1.0/System';
    const TYPE_BODY         = 'https://oparl.org/schema/1.0/Body';
    const TYPE_PAPER        = 'https://oparl.org/schema/1.0/Paper';
    const TYPE_ORGANIZATION = 'https://oparl.org/schema/1.0/Organization';
    
    /**
     * @param string $route
     * @param null|int $body
     * @param array $params
     *
     * @return string
     */
    public static function getOparlUrl($route, $body = null, $params = []) {
        $url = OPARL_10_ROOT;
        if ($body !== null) {
            $url .= '/body/' . $body;
        }
        
        foreach ($params as $i => $j) {
            $url .= '/' . $i . '/' . $j;
        }

        return $url;
    }
    
    /**
     */
    public static function system() {
        return [
            'id'                 => static::getOparlUrl('system'),
            'type'               => static::TYPE_SYSTEM,
            'oparlVersion'       => static::VERSION,
            'otherOparlVersions' => [],
            'body'               => static::getOparlUrl('bodyList'),
            'name'               => Yii::app()->params['projectTitle'],
            'contactEmail'       => Yii::app()->params['adminEmail'],
            'contactName'        => Yii::app()->params['adminEmailName'],
            'website'            => SITE_BASE_URL,
            'vendor'             => 'https://github.com/codeformunich/Muenchen-Transparent',
            'product'            => 'https://github.com/codeformunich/Muenchen-Transparent',
        ];
    }

    /**
     * @param int $body
     */
    public static function body($id) {
        $ba = Bezirksausschuss::model()->findByPk($id);
        return [
            'id'              => static::getOparlUrl('body', $ba->ba_nr),
            'type'            => static::TYPE_BODY,
            'system'          => static::getOparlUrl('system'),
            'contactEmail'    => Yii::app()->params['adminEmail'],
            'contactName'     => Yii::app()->params['adminEmailName'],
            'name'            => $ba->name,
            'shortName'       => ($ba->ba_nr > 0 ? 'BA ' . $ba->ba_nr : 'Stadtrat'),
            'website'         => $ba->getLink(),
            'organization'    => static::getOparlUrl('organizationList', $ba->ba_nr),
            'person'          => static::getOparlUrl('personList', $ba->ba_nr),
            'meeting'         => static::getOparlUrl('meetingList', $ba->ba_nr),
            'paper'           => static::getOparlUrl('paperList', $ba->ba_nr),
            'legislativeTerm' => static::legislativeTerms($ba->ba_nr),
        ];
    }
    
    /**
     * @param int $ba_nr
     *
     * @return array
     */
    public static function legislativeTerms($body_id) {
        return [
            0 => [
                'id'        => static::getOparlUrl('body', $body_id, ['term' => 0]),
                'body'      => static::getOparlUrl('body', $body_id),
                'name'      => 'Unbekannt',
                'startDate' => '0000-00-00',
                'endDate'   => '0000-00-00',
            ],
            1 => [
                'id'        => static::getOparlUrl('term', $body_id, ['term' => 1]),
                'body'      => static::getOparlUrl('body', $body_id),
                'name'      => '1996-2002',
                'startDate' => '1996-12-03',
                'endDate'   => '2002-12-03',
            ],
            2 => [
                'id'        => static::getOparlUrl('term', $body_id, ['term' => 2]),
                'body'      => static::getOparlUrl('body', $body_id),
                'name'      => '2002-2008',
                'startDate' => '2002-12-03',
                'endDate'   => '2008-12-03',
            ],
            3 => [
                'id'        => static::getOparlUrl('term', $body_id, ['term' => 3]),
                'body'      => static::getOparlUrl('body', $body_id),
                'name'      => '2008-2014',
                'startDate' => '2008-12-03',
                'endDate'   => '2014-12-03',
            ],
            4 => [
                'id'        => static::getOparlUrl('term', $body_id, ['term' => 4]),
                'body'      => static::getOparlUrl('body', $body_id),
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
        $gremium = Bezirksausschuss::model()->findByPk($id);
        $meetings    = [];
        $memberships = [];

        return [
            'id'             => static::getOrganizationId($gremium->ba_nr, $gremium->id),
            'type'           => static::TYPE_ORGANIZATION,
            'body'           => static::getOparlUrl($gremium->ba_nr),
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
    public static function fraktion(Fraktion $fraktion) {
        $fraktion = Fraktion::model()->findByPk($id);
        $meetings    = [];
        $memberships = [];

        return [
            'id'             => static::getOrganizationId($fraktion->ba_nr, $fraktion->id),
            'type'           => static::TYPE_ORGANIZATION,
            'body'           => static::getOparlUrl($fraktion->ba_nr),
            'name'           => $fraktion->getName(false),
            'shortName'      => $fraktion->getName(true),
            'meeting'        => $meetings,
            'membership'     => $memberships,
            'classification' => 'Fraktion',
        ];
    }
}
