<?php

class OParl10 {
    const VERSION = 'http://oparl.org/specs/1.0/';

    const TYPE_SYSTEM       = 'http://oparl.org/schema/1.0/System';
    const TYPE_BODY         = 'http://oparl.org/schema/1.0/Body';
    const TYPE_PAPER        = 'http://oparl.org/schema/1.0/Paper';
    const TYPE_ORGANIZATION = 'http://oparl.org/schema/1.0/Organization';

    /**
     * @param string $route
     * @param null|int $body
     * @param array $params
     *
     * @return string
     */
    public static function getOparlUrl($route, $body = null, $params = []) {
        /** @var CWebApplication $app */
        $app = Yii::app();
        if ($body !== null) {
            $params['body'] = $body;
        }

        return $app->createAbsoluteUrl('oparl/' . $route, $params);
    }

    /**
     * @param int $ba_nr
     * @param int $term_id
     *
     * @return string
     */
    public static function getLegislativeTermId($ba_nr, $term_id) {
        return static::getOparlUrl('term', $ba_nr, [ 'term' => $term_id]);
    }


    /**
     * @param int $ba_nr
     *
     * @return array
     */
    public static function getLegislativeTerms($ba_nr) {
        return [
            0 => [
                'id'        => static::getLegislativeTermId($ba_nr, 0),
                'body'      => static::getBodyId($ba_nr),
                'name'      => 'Unbekannt',
                'startDate' => '0000-00-00',
                'endDate'   => '0000-00-00',
            ],
            1 => [
                'id'        => static::getLegislativeTermId($ba_nr, 1),
                'body'      => static::getBodyId($ba_nr),
                'name'      => '1996-2002',
                'startDate' => '1996-12-03',
                'endDate'   => '2002-12-03',
            ],
            2 => [
                'id'        => static::getLegislativeTermId($ba_nr, 2),
                'body'      => static::getBodyId($ba_nr),
                'name'      => '2002-2008',
                'startDate' => '2002-12-03',
                'endDate'   => '2008-12-03',
            ],
            3 => [
                'id'        => static::getLegislativeTermId($ba_nr, 3),
                'body'      => static::getBodyId($ba_nr),
                'name'      => '2008-2014',
                'startDate' => '2008-12-03',
                'endDate'   => '2014-12-03',
            ],
            4 => [
                'id'        => static::getLegislativeTermId($ba_nr, 4),
                'body'      => static::getBodyId($ba_nr),
                'name'      => '2014-2020',
                'startDate' => '2014-12-03',
                'endDate'   => '2020-12-03',
            ],
        ];
    }

    /**
     * @param int $ba_nr
     *
     * @return string
     */
    public static function getBodyId($ba_nr) {
        return static::getOparlUrl('body', $ba_nr);
    }

    /**
     * @param Bezirksausschuss $ba
     *
     * @return array
     */
    public static function encodeBABody(Bezirksausschuss $ba) {
        return [
            'id'              => static::getBodyId($ba->ba_nr),
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
            'legislativeTerm' => static::getLegislativeTerms($ba->ba_nr),
        ];
    }

    /**
     * @param int $body
     * @param int $orga
     *
     * @return string
     */
    public static function getOrganizationId($body, $orga) {
        return static::getOparlUrl('organization', $body, [ 'orga' => $orga]);
    }

    /**
     * @param Gremium $gremium
     *
     * @return array
     */
    public static function encodeGremium(Gremium $gremium) {
        $meetings    = [];
        $memberships = [];

        return [
            'id'             => static::getOrganizationId($gremium->ba_nr, $gremium->id),
            'type'           => static::TYPE_ORGANIZATION,
            'body'           => static::getBodyId($gremium->ba_nr),
            'name'           => $gremium->getName(false),
            'shortName'      => $gremium->getName(true),
            'meeting'        => $meetings,
            'membership'     => $memberships,
            'classification' => $gremium->gremientyp,
        ];
    }

    /**
     * @param Fraktion $fraktion
     *
     * @return array
     */
    public static function encodeFraktion(Fraktion $fraktion) {
        $meetings    = [];
        $memberships = [];

        return [
            'id'             => static::getOrganizationId($fraktion->ba_nr, $fraktion->id),
            'type'           => static::TYPE_ORGANIZATION,
            'body'           => static::getBodyId($fraktion->ba_nr),
            'name'           => $fraktion->getName(false),
            'shortName'      => $fraktion->getName(true),
            'meeting'        => $meetings,
            'membership'     => $memberships,
            'classification' => 'Fraktion',
        ];
    }

    /**
     * @param int $body
     * @param int $paper
     *
     * @return string
     */
    public static function getPaperId($body, $paper) {
        return static::getOparlUrl('paper', $body, [ 'paper' => $paper]);
    }



    /**
     * @return string
     */
    public static function getSystemId() {
        return static::getOparlUrl('system');
    }

    /**
     * @return array
     */
    public static function encodeSystem() {
        return [
            'id'                 => static::getSystemId(),
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
}
