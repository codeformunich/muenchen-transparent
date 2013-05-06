<?

class RISSolrHelper {
	/**
	 * @param $text
	 * @return mixed
	 */
	public static function string_cleanup($text) {
		$chars = array("\r", chr(1), chr(2), chr(3), chr(4), chr(5), chr(6), chr(7), chr(8), chr(9), chr(10), chr(11), chr(12), chr(13), chr(14), chr(15), chr(16), chr(17), chr(18), chr(19), chr(20),
			chr(21), chr(22), chr(23), chr(24), chr(25), chr(26), chr(27), chr(28), chr(29), chr(30), chr(31));
		$repl = array();
		foreach ($chars as $c) $repl[] = " ";
		return str_replace($chars, $repl, iconv("UTF-8", "UTF-8", $text));
	}



	/**
	 * @return Solarium\Client
	 */
	public static function getSolrClient()
	{
		if (!isset($GLOBALS["SOLR_CLIENT"])) $GLOBALS["SOLR_CLIENT"] = new Solarium\Client(array(
			'endpoint' => array(
				'localhost' => array(
					'host' => '127.0.0.1',
					'port' => 8983,
					'path' => '/solr/collection1',
					'timeout' => 300,
				)
			)
		));
		/*
		$options = array (
			'hostname' => "localhost",
			'login'    => "ris",
			'password' => "e93kn4jLK",
			'port'     => "8983",
			'path'     => "/solr/" . $core,
		);
		return new SolrClient($options);
		*/

// create a client instance
		return $GLOBALS["SOLR_CLIENT"];
	}


	/**
	 * @param \Solarium\QueryType\Select\Result\Result $ergebnisse
	 * @return array();
	 */
	public static function ergebnisse2FeedData($ergebnisse) {
		$data = array();

		$dokumente    = $ergebnisse->getDocuments();
		$highlighting = $ergebnisse->getHighlighting();
		foreach ($dokumente as $dokument) {
			$model          = AntragDokument::getDocumentBySolrId($dokument->id);
			$link           = Yii::app()->createUrl("index/dokument", array("id" => str_replace("Document:", "", $dokument->id)));
			$highlightedDoc = $highlighting->getResult($dokument->id);
			$item           = array(
				"title"          => $model->name,
				"link"           => $link,
				"content"        => "",
				"dateCreated"    => RISTools::date_iso2timestamp($model->datum),
				"aenderung_guid" => $link
			);
			if ($highlightedDoc && count($highlightedDoc) > 0) {
				foreach ($highlightedDoc as $highlight) {
					$item["content"] .= nl2br(CHtml::encode(implode(' (...) ', $highlight))) . '<br/>';
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
		$solr = static::getSolrClient();
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