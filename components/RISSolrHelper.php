<?php

class RISSolrHelper
{
    /**
     * @param $text
     * @return mixed
     */
    public static function string_cleanup($text)
    {
        $chars = array("\r", chr(0), chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), chr(9), chr(10), chr(11), chr(12), chr(13), chr(14), chr(15), chr(16), chr(17), chr(18), chr(19), chr(20),
            chr(21), chr(22), chr(23), chr(24), chr(25), chr(26), chr(27), chr(28), chr(29), chr(30), chr(31));
        $repl  = array();
        foreach ($chars as $c) $repl[] = " ";
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
     * @param \Solarium\QueryType\Select\Result\Result $ergebnisse
     * @return array();
     */
    public static function ergebnisse2FeedData($ergebnisse)
    {
        $data = array();

        $dokumente    = $ergebnisse->getDocuments();
        $highlighting = $ergebnisse->getHighlighting();

        $purifier = new HtmlPurifier();
        $purifier->options = array('URI.AllowedSchemes'=>array(
            'http' => true,
            'https' => true,
        ));

        foreach ($dokumente as $dokument) {
            $model   = Dokument::getDocumentBySolrId($dokument->id);
            $risitem = $model->getRISItem();
            if (!$risitem) continue;

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
