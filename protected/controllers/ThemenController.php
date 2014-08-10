<?php

class ThemenController extends RISBaseController
{

	/**
	 *
	 */
	public function actionIndex() {
		$this->top_menu = "themen";
		$this->render("index");
	}


}