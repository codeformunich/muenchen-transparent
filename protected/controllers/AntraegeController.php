<?php

class AntraegeController extends RISBaseController
{

	public function actionAjaxThemenverwandte($id)
	{
		/** @var Antrag $antrag */
		$str    = "";
		$antrag = Antrag::model()->findByPk($id);
		if (!$antrag) {
			$str .= "<li>keine gefunden</li>";
		} else {
			$verwandt = $antrag->errateThemenverwandteAntraege(10);
			foreach ($verwandt as $verw) {

				$str .= "<li><div class='antraglink'><a href='" . CHtml::encode($verw->getLink()) . "' title='" . CHtml::encode($verw->getName()) . "'>";
				$str .= CHtml::encode($verw->getName()) . "</a></div>";

				$max_date = 0;
				foreach ($verw->dokumente as $dokument) {
					$dat = RISTools::date_iso2timestamp($dokument->datum);
					if ($dat > $max_date) $max_date = $dat;
				}

				$str .= "<div class='add_meta'>";
				$parteien = array();
				foreach ($verw->antraegePersonen as $person) {
					$name   = $person->person->name;
					$partei = $person->person->ratePartei($verw->gestellt_am);
					if (!$partei) {
						$parteien[$name] = array($name);
					} else {
						if (!isset($parteien[$partei])) $parteien[$partei] = array();
						$parteien[$partei][] = $person->person->name;
					}
				}

				$p_strs = array();
				foreach ($parteien as $partei => $personen) {
					$personen_net = array();
					foreach ($personen as $p) if ($p != $partei) $personen_net[] = $p;
					$str_p = "<span class='partei' title='" . CHtml::encode(implode(", ", $personen_net)) . "'>";
					$str_p .= CHtml::encode($partei);
					$str_p .= "</span>";
					$p_strs[] = $str_p;
				}
				if (count($p_strs) > 0) $str .= implode(", ", $p_strs) . ", ";

				if ($verw->ba_nr > 0) $str .= "<span title='" . CHtml::encode("Bezirksausschuss " . $verw->ba_nr . " (" . $verw->ba->name . ")") . "' class='ba'>BA " . $verw->ba_nr . "</span>, ";

				$str .= date((date("Y", $max_date) == date("Y") ? "d.m." : "d.m.Y"), $max_date);
				$str .= "</div>";

				$str .= "<div class='dokumente'>";
				$str .= (count($verw->dokumente) == 1 ? "1 Dokument" : count($verw->dokumente) . " Dokumente");
				$str .= "</div></li>\n";
			}
		}
		return $str;
	}

	/**
	 * @param int $id
	 */
	public function actionAnzeigen($id)
	{
		/** @var Antrag $antrag */
		$antrag = Antrag::model()->findByPk($id);
		if (!$antrag) {
			$this->render('/index/error', array("code" => 404, "message" => "Der Antrag wurde nicht gefunden"));
			return;
		}

		$this->render("anzeige", array(
			"antrag"         => $antrag,
			"themenverwandt" => $this->actionAjaxThemenverwandte($id),
		));
	}

}