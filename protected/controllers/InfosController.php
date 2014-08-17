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

	public function actionPersonen() {
		$this->top_menu = "personen";

		$this->render('personen', array(
			"fraktionen"            => StadtraetIn::getGroupedByFraktion(date("Y-m-d"), null),
		));
	}

}