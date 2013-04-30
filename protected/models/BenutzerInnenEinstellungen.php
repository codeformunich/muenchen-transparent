<?php

class BenutzerInnenEinstellungen {

	/** @var array */
	public $benachrichtigungen = array();

	/**
	 * @param string|null $data
	 */
	public function __construct($data) {
		if ($data == "") return;
		$data = (array)json_decode($data);

		if (!is_array($data)) return;
		foreach ($data as $key => $val) $this->$key = $val;
	}

	/**
	 * @return string
	 */
	public function toJSON() {
		return json_encode(get_object_vars($this));
	}

}
