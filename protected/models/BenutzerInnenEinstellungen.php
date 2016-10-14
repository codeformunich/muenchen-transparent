<?php

/**
 * Abstrahiert die benachrichtigungen eines Nutzers. Zur Speicherung werden RISSucheKrits mittels json in einen BLOB für
 * die Datenbank verwandelt.
 *
 * Class BenutzerInnenEinstellungen
 */
class BenutzerInnenEinstellungen
{

    /** @var array */
    public $benachrichtigungen = [];

    /** @var null|int */
    public $benachrichtigungstag = null;

    /**
     * @param string|null $data
     */
    public function __construct($data)
    {
        if ($data == "") return;
        $data = (array)json_decode($data, true);

        if (!is_array($data)) return;
        foreach ($data as $key => $val) $this->$key = $val;
    }

    /**
     * @return string
     */
    public function toJSON()
    {
        return json_encode(get_object_vars($this));
    }

}
