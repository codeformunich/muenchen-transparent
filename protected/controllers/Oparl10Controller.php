<?php

class OParl10Controller extends CController {
    const ITEMS_PER_PAGE = 100;

    public function actionSystem() {
        Header('Content-Type: application/json');
        $object = static::object('system', null);
        echo json_encode($object);
    }

    public function actionBody($id) {
        Header('Content-Type: application/json');
        $object = static::object('body', $id);
        echo json_encode($object);
    }

    public function actionObject($typ, $id) {
        Header('Content-Type: application/json');
        echo json_encode(static::object($typ, $id));
    }

    public function object($typ, $id, $body = null) {
        if      ($typ == 'system'  ) return OParl10Object::system();
        else if ($typ == 'fraktion') return OParl10Object::fraktion($id);
        else if ($typ == 'gremium' ) return OParl10Object::gremium($id);
        else if ($typ == 'person'  ) return OParl10Object::person($id, $body);
        else if ($typ == 'term'    ) return OParl10Object::terms($body)[$id];
        else if ($typ == 'body'    ) {
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
     */
    public function actionBodies() {
        Header('Content-Type: application/json');

        $bodies = [static::object('body', 0)];

        $bas = Bezirksausschuss::model()->findAll();
        foreach ($bas as $ba)
            $bodies[] = static::object('body', $ba->ba_nr);

        echo json_encode([
            'items'         => $bodies,
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => OParl10Object::getOparlListUrl('bodies'),
            'lastPage'      => OParl10Object::getOparlListUrl('bodies'),
            'numberOfPages' => 1,
        ]);
    }

    /**
     * @param int $body
     *
     * @return array
     */
    public function actionOrganizations($body) {
        Header('Content-Type: application/json');

        // FIXME: https://github.com/codeformunich/Muenchen-Transparent/issues/135
        $query = ($body > 0 ? 'ba_nr = ' . $body : 'ba_nr IS NULL');

        $organizations = [];

        $gremien = Gremium::model()->findAll($query);
        foreach ($gremien as $gremium)
            $organizations[] = static::object('gremium', $gremium->id);
        
        $fraktionen = Fraktion::model()->findAll($query);
        foreach ($fraktionen as $fraktion)
            $organizations[] = static::object('fraktion', $fraktion->id);
        
        echo json_encode([
            'items'         => $organizations,
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => OParl10Object::getOparlListUrl('organizations', $body),
            'lastPage'      => OParl10Object::getOparlListUrl('organizations', $body),
            'numberOfPages' => 1,
        ]);
    }

    /**
     */
    public function actionterms($body) {
        Header('Content-Type: application/json');
        
        echo json_encode([
            'items'         => OParl10Object::terms($body),
            'itemsPerPage'  => static::ITEMS_PER_PAGE,
            'firstPage'     => OParl10Object::getOparlListUrl('bodies'),
            'lastPage'      => OParl10Object::getOparlListUrl('bodies'),
            'numberOfPages' => 1,
        ]);
    }

}

//echo Gremium::model()->getCommandBuilder()->createFindCommand('gremien', $criteria)->text;
/*$criteria = new CDbCriteria();
$criteria->condition = 'ba_nr = ' . $body . ' AND id > ' . $id;
$criteria->order = 'id DESC';
$gremien = Gremium::model()->findAll($criteria);
*/
