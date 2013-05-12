<?php

class RISMetadaten {

	/**
	 * @return null|string
	 */
	public static function holeLetzteAktualisierung() {
		$result = Yii::app()->db->createCommand("SELECT meta_val FROM metadaten WHERE meta_key='letzte_aktualisierung'")->queryAll();
		var_dump($result);
		if (count($result) == 1) return $result[0]["meta_val"];
		return null;
	}

	/**
	 * @param string $datum
	 */
	public static function setzeLetzteAktualisierung($datum) {
		Yii::app()->db->createCommand("REPLACE INTO metadaten (meta_key, meta_val) VALUES ('letzte_aktualisierung', '" . addslashes($datum) . "')")->query();
	}
}
