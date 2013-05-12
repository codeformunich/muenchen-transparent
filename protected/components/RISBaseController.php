<?php

class RISBaseController extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

	public $top_menu = "";

	public $load_leaflet_css = false;
	public $load_leaflet_draw_css = false;


	private $_assetsBase = null;

	public function getAssetsBase()
	{
		if ($this->_assetsBase === null) {
			/** @var CWebApplication $app */
			$app = Yii::app();
			$this->_assetsBase = $app->assetManager->publish(
				Yii::getPathOfAlias('application.assets'),
				false,
				-1,
				defined('YII_DEBUG') && YII_DEBUG
			);


			$path = getcwd() . $this->_assetsBase . "/";
			if (!file_exists($path . "bas.js")) {
				$BAfeatures = array();
				/** @var array|Bezirksausschuss[] $BAs */
				$BAs = Bezirksausschuss::model()->findAll();
				foreach ($BAs as $ba) $BAfeatures[] = $ba->toGeoJSONArray();

				file_put_contents($path . "ba_features.js", "BA_FEATURES = " . json_encode($BAfeatures) . ";");
			};
		}
		return $this->_assetsBase;
	}
}