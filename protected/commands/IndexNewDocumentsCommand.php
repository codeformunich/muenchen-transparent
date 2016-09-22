<?php
/*
 * Indiziert alle Dokumente, die bisher nicht im Solr-Index waren
 */
class IndexNewDocumentsCommand extends CConsoleCommand
{
    public function run($args)
    {
        $client = RISSolrHelper::getSolrClient();
        
        $sql = Yii::app()->db->createCommand();
        $data = $sql->select("id")->from("dokumente")->order("id DESC")->queryColumn(["id"]);

        $anz = count($data);
        foreach ($data as $nr => $dok_id) {
            echo "\n$nr von $anz: $dok_id";
            /** @var Dokument $dokument */
            $dokument = Dokument::model()->findByPk($dok_id);
            
            if ($dokument == null) { // FIXME: Es gibt `dokumente`, die keine `Dokument`e sind, z.B.`3557949`
                echo " [failed]";
                continue;
            }
            
            if ($dokument->typ == Dokument::$TYP_RATHAUSUMSCHAU)
                $solr_id = "Rathausumschau\:" . $dokument->id;
            else if ($dokument->typ == Dokument::$TYP_STADTRAT_BESCHLUSS || $dokument->typ == Dokument::$TYP_BA_BESCHLUSS)
                $solr_id = "Ergebnis\:" . $dokument->id;
            else
                $solr_id = "Document\:" . $dokument->id;
            
            $query = $client->createSelect();
            $query->setQuery('id:%1%', array($solr_id));
            if ($client->select($query)->getNumFound() == 1) {
                echo " [skipped]";
                continue;
            }
            
            echo "\n" . $dokument->typ;
            echo "\n" . $solr_id;
            
            $dokument->geo_extract();
            $dokument->solrIndex();
        }
    }
}
