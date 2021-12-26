<?php

abstract class RISParser
{
    public abstract function parse(int $id): mixed;

    public abstract function parseAll(): void;

    public abstract function parseUpdate(): void;

    public abstract function parseQuickUpdate(): void;

    public static function text_simple_clean(string $text): string
    {
        $text = trim($text);
        $text = preg_replace("/<br ?\/?>/siU", "\n", $text);
        $text = str_replace("\n\n", "\n", $text);
        $text = str_replace("&nbsp;", " ", $text);
        $text = html_entity_decode($text, ENT_COMPAT, "UTF-8");
        return trim($text);
    }

    public static function text_clean_spaces(string $text): string
    {
        $text = str_replace("&nbsp;", " ", $text);
        $text = str_replace("<!-- Bitte prüfen! Texte werden nicht -->", "", $text);
        $text = preg_replace("/[ \\n]*<br ?\/>[ \\n]*/siu", "\n", $text);
        $text = preg_replace("/[ \\n]*<br ?\/>[ \\n]*/siu", "\n", $text);
        return trim(preg_replace("/<a[^>]*>[^<]*<\/a>/siU", "", $text));
    }

    public static function date_de2mysql(string $dat, ?string $fallback = null): ?string
    {
        $x = explode(".", trim($dat));
        if (count($x) != 3) return $fallback;
        if (strlen($x[0]) == 1) $x[0] = "0" . $x[0];
        if (strlen($x[1]) == 1) $x[1] = "0" . $x[1];
        if (strlen($x[2]) == 2) $x[2] = "20" . $x[2];
        return $x[2] . "-" . $x[1] . "-" . $x[0];
    }

    /** @param int [] */
    public function parseIDs(array $ids): void
    {
        foreach ($ids as $id) $this->parse($id);
    }
}
