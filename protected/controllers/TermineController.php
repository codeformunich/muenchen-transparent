<?php

class TermineController extends RISBaseController
{

	/**
	 *
	 */
	public function actionIndex() {
		$this->top_menu = "termine";

		$tage_zukunft       = 30;
		$tage_vergangenheit = 30;

		$termine_zukunft       = Termin::model()->termine_stadtrat_zeitraum(null, date("Y-m-d 00:00:00", time()), date("Y-m-d 00:00:00", time() + $tage_zukunft * 24 * 3600), true)->findAll();
		$termine_vergangenheit = Termin::model()->termine_stadtrat_zeitraum(null, date("Y-m-d 00:00:00", time() - $tage_vergangenheit * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();
		$termin_dokumente      = Termin::model()->neueste_stadtratsantragsdokumente(0, date("Y-m-d 00:00:00", time() - $tage_vergangenheit * 24 * 3600), date("Y-m-d 00:00:00", time()), false)->findAll();


		$this->render("index", array(
			"termine_zukunft"       => $termine_zukunft,
			"termine_vergangenheit" => $termine_vergangenheit,
			"termin_dokumente"      => $termin_dokumente,
			"tage_vergangenheit"    => $tage_vergangenheit,
			"tage_zukunft"          => $tage_zukunft,
		));
	}

	/**
	 * @param int $termin_id
	 */
	public function actionAnzeigen($termin_id) {
		$termin_id = IntVal($termin_id);

		$this->top_menu = "termine";

		/** @var Termin $antrag */
		$termin = Termin::model()->findByPk($termin_id);
		if (!$termin) {
			$this->render('/index/error', array("code" => 404, "message" => "Der Termin wurde nicht gefunden"));
			return;
		}

		$this->load_leaflet_css      = true;

		$this->render("anzeige", array(
			"termin" => $termin
		));
	}

	/**
	 * @param int $termin_id
	 */
	public function actionTopGeoExport($termin_id) {
		$termin_id = IntVal($termin_id);

		$this->top_menu = "termine";
		$this->load_leaflet_css      = true;

		/** @var Termin $sitzung */
		$termin = Termin::model()->findByPk($termin_id);

		$this->renderPartial("top_geo_export", array(
			"termin" => $termin
		));
	}


}