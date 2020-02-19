<?php

class RISSolrHelper
{
    /**
     * Entfernt die Zeichen \r und ASCII 0 bis 31 aus einem String
     *
     * @param $text
     * @return mixed
     */
    public static function string_cleanup($text)
    {
        $chars = ["\r"];
        $repl  = [" "];
        foreach (range(0, 31) as $i) {
            $chars[] = chr($i);
            $repl [] = " ";
        }
        return str_replace($chars, $repl, iconv("UTF-8", "UTF-8//TRANSLIT", $text));
    }
    
    /**
     * @return Solarium\Client
     */
    public static function getSolrClient()
    {
        if (!isset($GLOBALS["SOLR_CLIENT"])) $GLOBALS["SOLR_CLIENT"] = new Solarium\Client($GLOBALS["SOLR_CONFIG"]);
        // create a client instance
        return $GLOBALS["SOLR_CLIENT"];
    }


    /**
     * @param \Solarium\QueryType\Select\Result\DocumentInterface $ergebnisse
     * @return array();
     */
    public static function ergebnisse2FeedData($ergebnisse)
    {
        $data = array();

        $dokumente    = $ergebnisse->getDocuments();
        $highlighting = $ergebnisse->getHighlighting();

        $purifier = new CHtmlPurifier();
        $purifier->options = array('URI.AllowedSchemes'=>array(
            'http' => true,
            'https' => true,
        ));

        foreach ($dokumente as $dokument) {
            $model   = Dokument::getDocumentBySolrId($dokument->id);
            if (!$model) {
                continue;
            }
            $risitem = $model->getRISItem();
            if (!$risitem) {
                continue;
            }

            $link           = $risitem->getLink();
            $highlightedDoc = $highlighting->getResult($dokument->id);
            $item           = array(
                "title"          => $model->name . " (zu " . $risitem->getTypName() . " \"" . $risitem->getName() . "\"",
                "link"           => $link,
                "content"        => "",
                "dateCreated"    => RISTools::date_iso2timestamp(str_replace("T", " ", str_replace("Z", "", $dokument->sort_datum))),
                "aenderung_guid" => $link
            );
            if ($highlightedDoc && count($highlightedDoc) > 0) {
                foreach ($highlightedDoc as $highlight) {
                    $item["content"] .= $purifier->purify(implode(' (...) ', $highlight)) . '<br/>';
                }
            }
            $data[] = $item;
        }

        return $data;
    }

    /**
     *
     */
    public static function solr_optimize_ris()
    {
        $solr   = static::getSolrClient();
        $update = $solr->createUpdate();
        $update->addOptimize(true, false, 5);
        $solr->update($update);
    }

    /**
     * @param string $date
     * @return string
     */
    public static function mysql2solrDate($date)
    {
        //$dat = date_parse_from_format("Y-m-d H:i:s", $date);
        return str_replace(" ", "T", $date) . "Z";
    }

    /**
     * @param string $date
     * @return string
     */
    public static function solr2mysqlDate($date)
    {
        $x         = date_parse($date);
        $timestamp = gmmktime($x["hour"], $x["minute"], $x["second"], $x["month"], $x["day"], $x["year"]);
        return date("Y-m-d H:i:s", $timestamp);
    }

}
