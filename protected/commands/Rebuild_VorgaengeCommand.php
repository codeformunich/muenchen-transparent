<?php

class Rebuild_VorgaengeCommand extends CConsoleCommand {
	public function run($args) {
		if (!isset($args[0])) {
			echo "./yiic rebuild_vorgaenge [id|alle]\n";
			die();
		}
		if ($args[0] == "alle") {
			$sql = Yii::app()->db->createCommand();
			//$sql->select("id")->from("antraege")->where("id < 1245865 AND (seiten_anzahl = 0 OR seiten_anzahl = 9)")->order("id");
			$sql->select("id")->from("antraege")->where("id < 2349879")->order("id DESC");
			$data = $sql->queryColumn(array("id"));
			foreach ($data as $id) {
				echo $id . "\n";
				/** @var Antrag $antrag */
				$antrag = Antrag::model()->findByPk($id);
				$antrag->rebuildVorgaengeCache();
			}
		} else {
			/** @var Antrag $antrag */
			$antrag = Antrag::model()->findByPk($args[0]);
			$antrag->rebuildVorgaengeCache();
		}

	}
}