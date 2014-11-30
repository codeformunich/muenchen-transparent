<?php

class Reindex_DocumentCommand extends CConsoleCommand {
	public function run($args) {
		if (!isset($args[0]) || $args[0] <= 1) die("./yiic reindex_document [dokument-ID]\n");

		$sql = Yii::app()->db->createCommand();
		$sql->select("id")->from("antraege_dokumente")->where("id = " . IntVal($args[0]));
		$data = $sql->queryColumn(array("id"));

		$anz = count($data);
		foreach ($data as $nr => $dok_id) {
			echo "$nr / $anz => $dok_id\n";
			/** @var Dokument $dokument */
			$dokument = Dokument::model()->findByPk($dok_id);
			$dokument->reDownloadIndex();
		}
	}
}
