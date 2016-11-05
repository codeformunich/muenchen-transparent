<?php

/**
 * Enthält die Listenfilter für OParl. Das sind die zwingenden Filter sowie id für die Paginierng
 */
class OParl10Filter {
    public $id = null;
    public $created_since = null;
    public $created_until = null;
    public $modified_since = null;
    public $modified_until = null;

    /**
     * Fügt zu einem CDbCriteria-Objekt die zwingenden Filter nach Kapitel 2.5.5 hinzu
     *
     * @param $criteria CDbCriteria
     * @return  CDbCriteria
     */
    public function add_mandatory_filter($criteria) {
        if ($this->created_since  !== null) {
            $criteria->addCondition('created  >= :created_since');
            $criteria->params["created_since"]  = $this->created_since;
        }
        if ($this->created_until  !== null) {
            $criteria->addCondition('created  <= :created_until');
            $criteria->params["created_until"]  = $this->created_until;
        }
        if ($this->modified_since !== null) {
            $criteria->addCondition('modified >= :modified_since');
            $criteria->params["modified_since"] = $this->modified_since;
        }
        if ($this->modified_until !== null) {
            $criteria->addCondition('modified <= :modified_until');
            $criteria->params["modified_until"] = $this->modified_until;
        }
    }

    /**
     * Fügt zu einem CDbCriteria-Objekt die id für die Paginierung hinzu
     *
     * @param $criteria CDbCriteria
     * @param $items_per_page int
     * @return  CDbCriteria
     */
    public function add_pagination_filter($criteria, $items_per_page) {
        if ($this->id !== null) {
            $criteria->addCondition('id > :id');
            $criteria->params["id"] = $this->id;
        }
        $criteria->order = 'id ASC';
        $criteria->limit = $items_per_page;
    }
}
