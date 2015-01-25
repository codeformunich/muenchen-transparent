<?php

class TermineCalDAVDebug extends \Sabre\DAV\ServerPlugin {

	/** @var \Sabre\DAV\Server $server */
	protected $server;
	protected $logFile;

	protected $startTime;

	protected $logLevel = 5;

	protected $contentTypeWhiteList = array(
		'|^text/|',
		'|^application/xml|',
	);

	function __construct($logFile, $logLevel = 1) {
		$this->logFile = $logFile;

		$logFile = str_replace('%t',time(), $logFile);

		$this->logLevel = $logLevel;
		$this->startTime = time();

	}

	public function getPluginName() {

		return 'debuglogger';

	}

	/**
	 * Initializes the plugin
	 *
	 * @param \Sabre\DAV\Server $server
	 * @return void
	 */
	public function initialize(\Sabre\DAV\Server $server) {

		$this->server = $server;
		$this->server->on('beforeMethod', array($this, 'beforeMethod'), 5);
		$this->server->on('unknownMethod', array($this, 'unknownMethod'), 5);
		$this->server->on('report', array($this, 'report'), 5);
		$this->server->on('beforeGetProperties', array($this, 'beforeGetProperties'), 5);
		$this->log(2,'Initialized plugin. Request time ' . $this->startTime . ' (' . date(\DateTime::RFC2822,$this->startTime) . '). Version: ' . \Sabre\DAV\Version::VERSION);

	}

	/**
	 * Very first event to be triggered. This allows us to log the HTTP
	 * request.
	 *
	 * @param string $method
	 * @param string $uri
	 * @return void
	 */
	public function beforeMethod($method, $uri) {
		$str = $this->server->httpRequest->getBody();
		$string = stream_get_contents($str);

		$fp = fopen($this->logFile, "a");
		fwrite($fp, date("Y-m-d H:i:s") . " - $method $uri" . "\n");
		fwrite($fp, "POST Data:\n");
		fwrite($fp, $string ."\n");
		fclose($fp);

		$this->server->httpRequest->setBody($string);
	}

	/**
	 * This event is triggered when SabreDAV encounters a method that not
	 * handles by the core server. These are often handled by plugins.
	 *
	 * @param string $method
	 * @param string $uri
	 * @return void
	 */
	public function unknownMethod($method, $uri) {

		$this->log(3,'unknownMethod triggered. Method: ' . $method . ' uri: ' . ($uri?$uri:'(root)'));

	}
	/**
	 * This event is triggered when a report was requested.
	 *
	 * @param string $reportName
	 * @return void
	 */
	public function report($reportName) {

		$this->log(3,'Report requested: ' . $reportName);

	}

	/**
	 * This event it triggered when PROPFIND is done, or a subsystem
	 * requested properties.
	 *
	 * @param array $requestedProperties
	 * @return void
	 */
	public function beforeGetProperties($path, INode $node, $requestedProperties, $returnedProperties) {

		$this->log(3,'Properties requested for uri (' . $path . '):');
		$this->log(3,print_r($requestedProperties,true));

	}

	/**
	 * Appends a message to the log
	 *
	 * @param string $message
	 * @return void
	 */
	public function log($logLevel, $message) {

		if ($logLevel <= $this->logLevel) {
			$fp = fopen($this->logFile, "a");
			fwrite($fp, $message . "\n");
			fclose($fp);
		}

	}

}