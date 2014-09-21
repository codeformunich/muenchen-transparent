<?php

class Benachrichtigungen_VerschickenCommand extends CConsoleCommand
{

	/**
	 * @param BenutzerIn $benutzerIn
	 * @param array $data
	 * @throws Exception
	 * @return string
	 */
	private function verschickeNeueBenachrichtigungen_txt(&$benutzerIn, $data)
	{
		$path = Yii::getPathOfAlias('application.views.benachrichtigungen') . '/suchergebnisse_email_txt.php';
		if (!file_exists($path)) throw new Exception('Template ' . $path . ' does not exist.');
		ob_start();
		ob_implicit_flush(false);
		require($path);
		return ob_get_clean();

	}

	/**
	 * @param BenutzerIn $benutzerIn
	 * @param array $data
	 * @return string
	 * @throws Exception
	 */
	private function verschickeNeueBenachrichtigungen_html($benutzerIn, $data)
	{
		$path = Yii::getPathOfAlias('application.views.benachrichtigungen') . '/suchergebnisse_email_html.php';
		if (!file_exists($path)) throw new Exception('Template ' . $path . ' does not exist.');
		ob_start();
		ob_implicit_flush(false);
		require($path);
		return ob_get_clean();
	}

	/**
	 * @param BenutzerIn $benutzerIn
	 * @param int $zeitspanne
	 */
	private function benachrichtigeBenutzerIn($benutzerIn, $zeitspanne = 0)
	{
		$benachrichtigungen = $benutzerIn->getBenachrichtigungen();

		if ($zeitspanne > 0) {
			$neu_seit_ts = time() - $zeitspanne * 24 * 3600;
			$neu_seit    = date("Y-m-d H:i:s", $neu_seit_ts);
		} else {
			$neu_seit    = $benutzerIn->datum_letzte_benachrichtigung;
			$neu_seit_ts = RISTools::date_iso2timestamp($neu_seit);
		}

		$ergebnisse = array(
			"antraege"  => array(),
			"termine"   => array(),
			"vorgaenge" => array(),
		);

		$sql = Yii::app()->db->createCommand();
		$sql->select("id")->from("antraege_dokumente")->where("datum >= '" . addslashes($neu_seit) . "'");
		$data = $sql->queryColumn(array("id"));
		if (count($data) > 0) {

			$document_ids = array();
			foreach ($data as $did) $document_ids[] = "id:\"Document:$did\"";

			foreach ($benachrichtigungen as $benachrichtigung) {
				$e = $benutzerIn->queryBenachrichtigungen($document_ids, $benachrichtigung);
				foreach ($e as $f) {
					$d           = explode(":", $f["id"]);
					$dokument_id = IntVal($d[1]);
					$dokument    = AntragDokument::getCachedByID($dokument_id);
					if (!$dokument) continue;
					if ($dokument->antrag_id > 0) {
						if (!isset($ergebnisse["antraege"][$dokument->antrag_id])) $ergebnisse["antraege"][$dokument->antrag_id] = array(
							"antrag"    => $dokument->antrag,
							"dokumente" => array()
						);
						if (!isset($ergebnisse["antraege"][$dokument->antrag_id]["dokumente"][$dokument_id])) $ergebnisse["antraege"][$dokument->antrag_id]["dokumente"][$dokument_id] = array(
							"dokument" => AntragDokument::model()->findByPk($dokument_id),
							"queries"  => array()
						);
						$ergebnisse["antraege"][$dokument->antrag_id]["dokumente"][$dokument_id]["queries"][] = $benachrichtigung;
					} elseif ($dokument->termin_id > 0) {
						if (!isset($ergebnisse["termine"][$dokument->termin_id])) $ergebnisse["termine"][$dokument->termin_id] = array(
							"termin"    => $dokument->termin,
							"dokumente" => array()
						);
						if (!isset($ergebnisse["termine"][$dokument->termin_id]["dokumente"][$dokument_id])) $ergebnisse["termine"][$dokument->termin_id]["dokumente"][$dokument_id] = array(
							"dokument" => AntragDokument::model()->findByPk($dokument_id),
							"queries"  => array()
						);
						$ergebnisse["termine"][$dokument->termin_id]["dokumente"][$dokument_id]["queries"][] = $benachrichtigung;
					} else {
						echo "Unbekanntes Ergebnis: Dokument-ID " . $dokument->id;
					}
				}
			}
		}

		foreach ($benutzerIn->abonnierte_vorgaenge as $vorgang) {
			foreach ($vorgang->antraege as $ant) {
				if (RISTools::date_iso2timestamp($ant->datum_letzte_aenderung) < $neu_seit_ts) continue;
				if (!isset($ergebnisse["vorgaenge"][$vorgang->id])) $ergebnisse["vorgaenge"][$vorgang->id] = array("vorgang" => $vorgang->wichtigstesRisItem()->getName(true), "neues" => array());
				$ergebnisse["vorgaenge"][$vorgang->id]["neues"][] = $ant;
			}
			foreach ($vorgang->dokumente as $dok) {
				if (RISTools::date_iso2timestamp($dok->datum) < $neu_seit_ts) continue;
				if (!isset($ergebnisse["vorgaenge"][$vorgang->id])) $ergebnisse["vorgaenge"][$vorgang->id] = array("vorgang" => $vorgang->wichtigstesRisItem()->getName(true), "neues" => array());
				$ergebnisse["vorgaenge"][$vorgang->id]["neues"][] = $dok;
			}
			foreach ($vorgang->ergebnisse as $erg) {
				if (RISTools::date_iso2timestamp($erg->datum_letzte_aenderung) < $neu_seit_ts) continue;
				if (!isset($ergebnisse["vorgaenge"][$vorgang->id])) $ergebnisse["vorgaenge"][$vorgang->id] = array("vorgang" => $vorgang->wichtigstesRisItem()->getName(true), "neues" => array());
				$ergebnisse["vorgaenge"][$vorgang->id]["neues"][] = $erg;
			}
		}

		if (count($ergebnisse["antraege"]) == 0 && count($ergebnisse["termine"]) == 0 && count($ergebnisse["vorgaenge"]) == 0) return;

		$mail_txt  = $this->verschickeNeueBenachrichtigungen_txt($benutzerIn, $ergebnisse);
		$mail_html = $this->verschickeNeueBenachrichtigungen_html($benutzerIn, $ergebnisse);
		RISTools::send_email($benutzerIn->email, "Neues im Münchner RIS", $mail_txt, $mail_html);

		$benutzerIn->datum_letzte_benachrichtigung = new CDbExpression("NOW()");
		$benutzerIn->save();
	}

	public function run($args)
	{
		if (count($args) >= 2) {
			if (is_numeric($args[0])) {
				$benutzerIn = BenutzerIn::model()->findByPk($args[0]);
			} else {
				$benutzerIn = BenutzerIn::model()->findByAttributes(array("email" => $args[0]));
			}
			if (!$benutzerIn) die("BenutzerIn nicht gefunden.\n");
			/** @var BenutzerIn $benutzerIn */
			$this->benachrichtigeBenutzerIn($benutzerIn, $args[1]);
		} else {
			/** @var BenutzerIn[] $benutzerInnen */
			$benutzerInnen = BenutzerIn::model()->findAll();
			foreach ($benutzerInnen as $benutzerIn) try {
				$this->benachrichtigeBenutzerIn($benutzerIn);
			} catch (Exception $e) {
				var_dump($e);
			}
		}
	}
}