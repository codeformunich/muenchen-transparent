<?php

class OParl10Controller extends CController {
    public function actionSystem() {
        Header("Content-Type: application/json");
        $object = static::object("system", null);
        echo json_encode($object);
    }

    public function actionBody($id) {
        Header("Content-Type: application/json");
        $object = static::object("body", $id);
        echo json_encode($object);
    }

    public function actionObject($name, $id) {
        Header("Content-Type: application/json");
        $object = static::object($name, $id);
        echo json_encode($object);
    }

    public function object($name, $id) {
        if      ($name == "system") return OParl10Object::system();
        else if ($name == "body"  ) return OParl10Object::body($id);
        else return ["error" => "Object of typ " + $name + " not found."];
    }

    public function pagination($name, $id = 0) {
        
    }
    
    /**
     */
    public function actionBodyList() {
        Header("Content-Type: application/json");

        $bodies = [];

        $bas = Bezirksausschuss::model()->findAll();
        foreach ($bas as $ba) {
            $bodies[] = static::encodeBABody($ba);
        }

        echo json_encode([
            'items'        => $bodies,
            'itemsPerPage' => 100,
        ]);
    }

    /**
     * @param int $body
     *
     * @return array
     */
    public function actionOrganizationList($body) {
        Header("Content-Type: application/json");

        $organizations = [];

        $body    = ($body > 0 ? IntVal($body) : null);
        $gremien = Gremium::model()->findAllByAttributes(['ba_nr' => $body]);
        foreach ($gremien as $gremium) {
            $organizations[] = static::encodeGremium($gremium);
        }
        $fraktionen = Fraktion::model()->findAllByAttributes(['ba_nr' => $body]);
        foreach ($fraktionen as $fraktion) {
            $organizations[] = static::encodeFraktion($fraktion);
        }

        echo json_encode([
            'items'        => $organizations,
            'itemsPerPage' => 100,
        ]);
    }

}
