<?php

abstract class RISParser
{
    public abstract function parse($id);

    public abstract function parseSeite($seite, $first);

    public abstract function parseAlle();

    public abstract function parseUpdate();

    /**
     * @param string $text
     * @return string
     */
    public static function text_simple_clean($text)
    {
        $text = trim($text);
        $text = preg_replace("/<br ?\/?>/siU", "\n", $text);
        $text = str_replace("\n\n", "\n", $text);
        $text = str_replace("&nbsp;", " ", $text);
        $text = html_entity_decode($text, ENT_COMPAT, "UTF-8");
        return trim($text);
    }

    public static function text_clean_spaces($text)
    {
        $text = str_replace("&nbsp;", " ", $text);
        $text = str_replace("<!-- Bitte prüfen! Texte werden nicht -->", "", $text);
        $text = preg_replace("/[ \\n]*<br ?\/>[ \\n]*/siu", "\n", $text);
        $text = preg_replace("/[ \\n]*<br ?\/>[ \\n]*/siu", "\n", $text);
        return trim(preg_replace("/<a[^>]*>[^<]*<\/a>/siU", "", $text));
    }

    /**
     * @param string $dat
     * @param null|string $fallback
     * @return null|string
     */
    public static function date_de2mysql($dat, $fallback = null)
    {
        $x = explode(".", trim($dat));
        if (count($x) != 3) return $fallback;
        if (strlen($x[0]) == 1) $x[0] = "0" . $x[0];
        if (strlen($x[1]) == 1) $x[1] = "0" . $x[1];
        if (strlen($x[2]) == 2) $x[2] = "20" . $x[2];
        return $x[2] . "-" . $x[1] . "-" . $x[0];
    }

    /** @param int [] */
    public static function parseIDs($ids)
    {
        foreach ($ids as $id) static::parse($id);
    }


}