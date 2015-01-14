<?php

class RISMetadaten
{

    /**
     * @return null|string
     */
    public static function holeLetzteAktualisierung()
    {
        $result = Yii::app()->db->createCommand("SELECT meta_val FROM metadaten WHERE meta_key='letzte_aktualisierung'")->queryAll();
        if (count($result) == 1) return $result[0]["meta_val"];
        return null;
    }

    /**
     * @param string $datum
     */
    public static function setzeLetzteAktualisierung($datum)
    {
        Yii::app()->db->createCommand("REPLACE INTO metadaten (meta_key, meta_val) VALUES ('letzte_aktualisierung', '" . addslashes($datum) . "')")->query();
    }

    /**
     * @return array
     */
    public static function getStats()
    {
        $result = Yii::app()->db->createCommand("SELECT * FROM metadaten WHERE meta_key IN ('anzahl_dokumente', 'anzahl_dokumente_1w', 'anzahl_seiten', 'anzahl_seiten_1w', 'letzte_aktualisierung')")->queryAll();
        $ret    = array();
        foreach ($result as $res) $ret[$res["meta_key"]] = $res["meta_val"];
        return $ret;
    }


    /**
     */
    public static function recalcStats()
    {
        $result = Yii::app()->db->createCommand("SELECT COUNT(*) anzahl_dokumente, SUM(seiten_anzahl) anzahl_seiten FROM dokumente")->queryAll();
        Yii::app()->db->createCommand("REPLACE INTO metadaten (meta_key, meta_val) VALUES ('anzahl_dokumente', '" . IntVal($result[0]["anzahl_dokumente"]) . "')")->query();
        Yii::app()->db->createCommand("REPLACE INTO metadaten (meta_key, meta_val) VALUES ('anzahl_seiten', '" . IntVal($result[0]["anzahl_seiten"]) . "')")->query();

        $result = Yii::app()->db->createCommand("SELECT COUNT(*) anzahl_dokumente, SUM(seiten_anzahl) anzahl_seiten FROM dokumente WHERE datum > NOW() - INTERVAL 1 WEEK")->queryAll();
        Yii::app()->db->createCommand("REPLACE INTO metadaten (meta_key, meta_val) VALUES ('anzahl_dokumente_1w', '" . IntVal($result[0]["anzahl_dokumente"]) . "')")->query();
        Yii::app()->db->createCommand("REPLACE INTO metadaten (meta_key, meta_val) VALUES ('anzahl_seiten_1w', '" . IntVal($result[0]["anzahl_seiten"]) . "')")->query();
    }
}
