<?php

class AntraegeController extends RISBaseController
{

	public function actionAjaxThemenverwandte($id) {
		/** @var Antrag $antrag */
		$str = "";
		$antrag = Antrag::model()->findByPk($id);
		if (!$antrag) {
			$str .= "<li>keine gefunden</li>";
		} else {
			$verwandt = $antrag->errateThemenverwandteAntraege(10);
			foreach ($verwandt as $verw) {
				$str .= '<li>';
				$str .= CHtml::link($verw->getName(), $verw->getLink());
				$str .= '</li>';
			}
		}
		return $str;
	}

	/**
	 * @param int $id
	 */
	public function actionAnzeigen($id) {
		/** @var Antrag $antrag */
		$antrag = Antrag::model()->findByPk($id);
		if (!$antrag) {
			$this->render('/index/error', array("code" => 404, "message" => "Der Antrag wurde nicht gefunden"));
			return;
		}

		$this->render("anzeige", array(
			"antrag" => $antrag,
			"themenverwandt" => $this->actionAjaxThemenverwandte($id),
		));
	}

}