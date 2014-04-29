<?php

class Benachrichtigungen_VerschickenCommand extends CConsoleCommand {

	/**
	 * @param BenutzerIn $benutzerIn
	 * @param array $data
	 * @throws Exception
	 * @return string
	 */
	private function verschickeNeueBenachrichtigungen_txt(&$benutzerIn, $data)
	{
		$path = Yii::getPathOfAlias('application.views.benachrichtigungen').'/suchergebnisse_email_txt.php';
		if(!file_exists($path)) throw new Exception('Template '.$path.' does not exist.');
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
		$path = Yii::getPathOfAlias('application.views.benachrichtigungen').'/suchergebnisse_email_html.php';
		if(!file_exists($path)) throw new Exception('Template '.$path.' does not exist.');
		ob_start();
		ob_implicit_flush(false);
		require($path);
		return ob_get_clean();
	}

	public function run($args) {
		/** @var BenutzerIn[] $benutzerInnen */
		$benutzerInnen = BenutzerIn::model()->findAll();
		foreach ($benutzerInnen as $benutzerIn) {

			$benachrichtigungen = $benutzerIn->getBenachrichtigungen();

			$neu_seit = $benutzerIn->datum_letzte_benachrichtigung;
			$sql      = Yii::app()->db->createCommand();
			$sql->select("id")->from("antraege_dokumente")->where("datum >= '" . addslashes($neu_seit) . "'");
			$data = $sql->queryColumn(array("id"));
			if (count($data) == 0) return;

			$document_ids = array();
			foreach ($data as $did) $document_ids[] = "id:\"Document:$did\"";

			$ergebnisse = array(
				"antraege" => array(),
				"termine"  => array()
			);
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

			if (count($ergebnisse["antraege"]) == 0 && count($ergebnisse["termine"]) == 0) return;

			$mail_txt  = $this->verschickeNeueBenachrichtigungen_txt($benutzerIn, $ergebnisse);
			$mail_html = $this->verschickeNeueBenachrichtigungen_html($benutzerIn, $ergebnisse);

			$mail = new Zend\Mail\Message();
			$mail->setFrom(Yii::app()->params["adminEmail"], Yii::app()->params["adminEmailName"]);
			$mail->addTo($benutzerIn->email, $benutzerIn->email);
			$mail->setSubject("Neue Dokumente im Münchner RIS");

			$mail->setEncoding("UTF-8");

			$text_part          = new Zend\Mime\Part($mail_txt);
			$text_part->type    = "text/plain";
			$text_part->charset = "UTF-8";
			$html_part          = new Zend\Mime\Part($mail_html);
			$html_part->type    = "text/html";
			$html_part->charset = "UTF-8";
			$mimem              = new Zend\Mime\Message();
			$mimem->setParts(array($text_part, $html_part));

			$mail->setBody($mimem);
			$mail->getHeaders()->get('content-type')->setType('multipart/alternative');

			$transport = new Zend\Mail\Transport\Sendmail();
			$transport->send($mail);

			$benutzerIn->datum_letzte_benachrichtigung = new CDbExpression("NOW()");
			$benutzerIn->save();
		}
	}
}