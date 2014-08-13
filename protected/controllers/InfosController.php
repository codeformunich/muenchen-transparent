<?php

class InfosController extends RISBaseController
{
	public function actionSoFunktioniertStadtpolitik()
	{
		$this->top_menu = "so_funktioniert";
		$this->render('so_funktioniert_stadtpolitik');
	}

	public function actionImpressum()
	{
		$this->top_menu = "impressum";
		$this->render('impressum');
	}

	public function actionDatenschutz()
	{
		$this->top_menu = "datenschutz";
		$this->render('datenschutz');
	}

	public function actionAnsprechpartnerInnen() {
		$this->top_menu = "ansprechpartnerInnen";

		$this->render('ansprechpartnerInnen', array(
			"fraktionen"            => StadtraetIn::getGroupedByFraktion(date("Y-m-d"), null),
		));
	}

}