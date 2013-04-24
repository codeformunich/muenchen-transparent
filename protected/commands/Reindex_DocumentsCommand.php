<?php

class Reindex_DocumentsCommand extends CConsoleCommand {
	public function run($args) {

		$sql = Yii::app()->db->createCommand();
		$sql->select("id")->from("antraege_dokumente")->where("id >= 910956")->order("id");
		$data = $sql->queryColumn(array("id"));

		$anz = count($data);
		foreach ($data as $nr => $dok_id) {
			echo "$nr / $anz => $dok_id\n";
			/** @var AntragDokument $dokument */
			$dokument = AntragDokument::model()->findByPk($dok_id);
			$dokument->reDownloadIndex();
		}
	}
}