<?php

use Solarium\Core\Query\Result\ResultInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class RISSolrHelper
{
    private static Solarium\Client $SOLR_CLIENT;

    /**
     * Removes \r characters and ASCII 0 to 31 from a string
     */
    public static function string_cleanup(string $text): string
    {
        $chars = ["\r"];
        $repl  = [" "];
        foreach (range(0, 31) as $i) {
            $chars[] = chr($i);
            $repl [] = " ";
        }
        return str_replace($chars, $repl, iconv("UTF-8", "UTF-8//TRANSLIT", $text));
    }
    
    public static function getSolrClient(): Solarium\Client
    {
        if (!isset(static::$SOLR_CLIENT)) {
            $adapter = new Solarium\Core\Client\Adapter\Curl();
            static::$SOLR_CLIENT = new Solarium\Client($adapter, new EventDispatcher(), $GLOBALS["SOLR_CONFIG"]);
        }
        // create a client instance
        return static::$SOLR_CLIENT;
    }


    public static function ergebnisse2FeedData(ResultInterface $results): array
    {
        $data = [];

        $dokumente    = $results->getDocuments();
        $highlighting = $results->getHighlighting();

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

    public static function solr_optimize_ris(): void
    {
        $solr   = static::getSolrClient();
        $update = $solr->createUpdate();
        $update->addOptimize(true, false, 5);
        $solr->update($update);
    }

    public static function mysql2solrDate(string $date): string
    {
        //$dat = date_parse_from_format("Y-m-d H:i:s", $date);
        return str_replace(" ", "T", $date) . "Z";
    }

    public static function solr2mysqlDate(string $date): string
    {
        $x         = date_parse($date);
        $timestamp = gmmktime($x["hour"], $x["minute"], $x["second"], $x["month"], $x["day"], $x["year"]);
        return date("Y-m-d H:i:s", $timestamp);
    }

}
