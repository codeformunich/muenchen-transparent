<?php

class OParlController extends CController
{

    /**
     * @param string $route
     * @param null|int $body
     * @param array $params
     *
     * @return string
     */
    public static function getOparlUrl($route, $body = null, $params = [])
    {
        /** @var CWebApplication $app */
        $app = Yii::app();
        if ($body !== null) {
            $params['body'] = $body;
        }

        return $app->createAbsoluteUrl('oparl/' . $route, $params);
    }

    /**
     */
    public function actionSystem()
    {
        Header("Content-Type: application/json");
        echo json_encode([
            'id'                 => 'de.muenchen-transparent',
            'type'               => OParl::TYPE_SYSTEM,
            'oparlVersion'       => OParl::VERSION,
            'otherOparlVersions' => [],
            'body'               => $this->getOparlUrl('body'),
            'name'               => Yii::app()->params['projectTitle'],
            'contactEmail'       => Yii::app()->params['adminEmail'],
            'contactName'        => Yii::app()->params['adminEmailName'],
            'website'            => SITE_BASE_URL,
            'vendor'             => 'https://github.com/codeformunich/Muenchen-Transparent',
            'product'            => 'https://github.com/codeformunich/Muenchen-Transparent',
        ]);
    }

    /**
     */
    public function actionBodyList()
    {
        Header("Content-Type: application/json");

        $bodies = [];

        $bas = Bezirksausschuss::model()->findAll();
        foreach ($bas as $ba) {
            $bodies[] = $ba->toOParlBody();
        }

        echo json_encode([
            'items'        => $bodies,
            'itemsPerPage' => 100,
        ]);
    }

    /**
     * @param int $body
     */
    public function actionBody($body)
    {
        Header("Content-Type: application/json");

        $ba = Bezirksausschuss::model()->findByPk($body);
        echo json_encode($ba->toOParlBody());
    }

    /**
     * @param int $body
     */
    public function actionPaperList($body)
    {
        Header("Content-Type: application/json");

        echo json_encode([]); // @TOOD
    }

    /**
     * @param int $body
     * @param int $paper
     */
    public function actionPaper($body, $paper)
    {
        Header("Content-Type: application/json");

        $antrag = Antrag::model()->findByPk($paper);

        echo json_encode($antrag->toOParlPaper()); // @TOOD
    }
}
