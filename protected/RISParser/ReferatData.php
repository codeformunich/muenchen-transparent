<?php

declare(strict_types=1);

class ReferatData
{
    public int $id;
    public string $name;
    public int $referentInId;
    public string $referentInName;

    /**
     * @return string[]
     */
    public static function splitPage(string $html): array
    {
        $payload = explode('<footer>', $html);
        $payload = explode('container-fluid px-0', $payload[0]);
        $parts = explode('card h-100 p-2', $payload[1]);
        array_shift($parts); // The first part is mostly empty

        return $parts;
    }

    public static function parseFromHtml(string $html): ?self
    {
        if (!preg_match('/href="\.\.\/detail\/(?<id>\d+)"[^>]*>(?<title>[^<]*)<\/a>/siuU', $html, $match)) {
            throw new ParsingException('Not found: id/title');
        }
        $entry = new self();
        $entry->id = intval($match['id']);
        $entry->name = $match['title'];

        if (!preg_match('/href="\.\.\/\.\.\/person\/detail\/(?<id>\d+)\?tab"[^>]*>(?<title>[^<]*)<\/a>/siuU', $html, $match)) {
            throw new ParsingException('Not found: referentIn');
        }
        $entry->referentInId = intval($match['id']);
        $entry->referentInName = preg_replace('/^(Herr|Frau) /siu', '', $match['title']);

        return $entry;
    }
}
