<?php

/**
 * Enhält alle actions für OParl 1.0 sowie einige Hilfsmethoden
 */
class OParl10Controller extends CController {
    const VERSION = 'https://oparl.org/specs/1.0/';

    /**
     * Erzeugt die URL zu einem einzelnen OParl-Objekt
     */
    public static function getOparlObjectUrl($typ, $id, $subtype = null)
    {
        if ($typ == 'system') {
            return OPARL_10_ROOT;
        }

        if ($subtype != null) {
            return OPARL_10_ROOT . '/' . $typ . '/' . $subtype . '/' . $id;
        }

        return OPARL_10_ROOT . '/' . $typ . '/' . $id;
    }

    /**
     * Erzeugt die URL zu einer externen Liste mit OParl-Objekten
     *
     * @param $typ int
     * @param $filter OParl10Filter
     * @param $body int
     *
     * @return String
     */
    public static function getOparlListUrl($typ, $filter, $body) {
        $url = OPARL_10_ROOT;
        if ($body != null) $url .= '/body/' . $body;
        $url .= '/list/' . $typ;
        if ($filter != null && !$filter->is_empty()) $url .= '?' . $filter->to_url_params();

        return $url;
    }

    /**
     * Wandelt einen MySQL timestamp in einen String mit Datum, Zeit und Zeitzone im dem vom OParl genutzten Format um
     * (entspricht ISO 8601)
     */
    public static function mysqlToOparlDateTime($in) {
        return (new DateTime($in, new DateTimeZone(DEFAULT_TIMEZONE)))->format(DateTime::ATOM);
    }

    /**
     * Umkehrfunktion zu mysqlToOparlDateTime
     */
    public static function oparlDateTimeToMysql($in) {
        return DateTime::createFromFormat(DateTime::ISO8601, $in)->format('Y-m-d H:i:s');
    }

    /**
     * Gibt das Array $data als OParl-Objekt zusammen mit den korrekten Headern aus.
     */
    public static function asOParlJSON($data) {
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Gibt das 'oparl:System'-Objekt als JSON aus
     */
    public function actionSystem() {
        self::asOParlJSON(OParl10Object::get('system', null));
    }

    /**
     * Gibt ein beliebiges Objekt außer 'oparl:System' als JSON aus
     */
    public function actionObject($typ, $id, $subtype = null) {
        self::asOParlJSON(OParl10Object::get($typ, $id, $subtype));
    }

    /**
     * Gibt die externen Liste mit den 'oparl:Body'-Objekten als JSON aus
     */
    public function actionExternalListBody($id = null, $created_since = null, $created_until = null, $modified_since = null, $modified_until = null) {
        $filter = new OParl10Filter();
        $filter->id  = $id;
        $filter->created_since  = $created_since;
        $filter->created_until  = $created_until;
        $filter->modified_since = $modified_since;
        $filter->modified_until = $modified_until;
        self::asOParlJSON(OParl10List::get('body', null, $filter));
    }

    /**
     * Gibt ein beliebiges Objekt außer 'oparl:System' als JSON aus
     */
    public function actionExternalList($typ, $body, $id = null, $created_since = null, $created_until = null, $modified_since = null, $modified_until = null) {
        $filter = new OParl10Filter();
        $filter->id  = $id;
        $filter->created_since  = $created_since;
        $filter->created_until  = $created_until;
        $filter->modified_since = $modified_since;
        $filter->modified_until = $modified_until;
        self::asOParlJSON(OParl10List::get($typ, $body, $filter));
    }

    /**
     * Gibt die Datei mit zum Dokument $id mit den zu $mode gehörenden headern aus
     */
    public function actionFileaccess($mode, $id) {
        $dokument = Dokument::model()->findByPk($id);
        if ($dokument === null) {
            header('HTTP/1.0 404 Not Found');
            return;
        }

        $content = $dokument->getDateiInhalt();
        if ($content === null) {
            header('HTTP/1.0 410 Gone');
            return;
        } else {
            echo $content;
        }

        if ($mode == 'download') {
            header('Content-Disposition: attachment; filename="' . $dokument->getDateiname() . '"');
        }
    }

    public function actionSuche($searchterm)
    {
        $krits = new RISSucheKrits();
        $krits->addKrit('volltext', $searchterm);

        $solr = RISSolrHelper::getSolrClient();
        $select = $solr->createSelect();

        $krits->addKritsToSolr($select);

        $select->setRows(50);
        $select->addSort('sort_datum', $select::SORT_DESC);

        // Tag hinzufügen, der den Namen entspricht
        foreach ($select->getFilterQueries() as $filter_query) {
            $filter_query->addTag($filter_query->getKey());
        }

        /** @var Solarium\QueryType\Select\Query\Component\Highlighting\Highlighting $hl */
        $hl = $select->getHighlighting();
        $hl->setFields('text, text_ocr, antrag_betreff');
        $hl->setSimplePrefix('<b>');
        $hl->setSimplePostfix('</b>');

        $facet_field_namess = [
            ['antrag_typ', 'antrag_typ', 'Dokumenttypen'],
            ['antrag_wahlperiode', 'antrag_wahlperiode', 'Wahlperiode'],
            ['dokument_bas', 'ba', 'Bezirksauschüsse'],
            ['referat_id', 'referat', 'Referat'],
        ];

        $facetSet = $select->getFacetSet();
        foreach ($facet_field_namess as $facet_field_names) {
            $facetSet
                ->createFacetField($facet_field_names[0])
                ->setField($facet_field_names[0])
                ->setExcludes([$facet_field_names[0]]);
        }

        try {
            $ergebnisse = $solr->select($select);
        } catch (Exception $e) {
            $this->render('error', ["code" => 500, "message" => "Ein Fehler bei der Suche ist aufgetreten"]);
            Yii::app()->end(500);
        }

        $search_results = [];
        $dokumente = $ergebnisse->getDocuments();
        foreach ($dokumente as $dokument) {
            $dok = Dokument::getDocumentBySolrId($dokument->id, true);
            if (!$dok) {
                $search_results[] = ['error' => "Dokument nicht gefunden: " . $dokument->id];
                continue;
            }

            $search_results[] = OParl10Object::get('file', $dok->id);
        }

        self::asOParlJSON($search_results);
    }
}
