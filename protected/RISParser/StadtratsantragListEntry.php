<?php

declare(strict_types=1);

class StadtratsantragListEntry
{
    public string $titleShortened;
    public ?\DateTime $gestelltAm = null;
    public int $id;
    public string $link;

    /**
     * @return StadtratsantragListEntry[]
     */
    public static function parseHtmlList(string $htmlList): array
    {
        preg_match_all('/<li.*<\/li>/siuU', $htmlList, $matches);
        $parsedObjects = [];
        foreach ($matches[0] as $match) {
            $obj = static::parseFromHtml($match);
            if ($obj) {
                $parsedObjects[] = $obj;
            }
        }
        return $parsedObjects;
    }

    public static function parseFromHtml(string $html): ?self
    {
        if (!preg_match('/<a class="headline-link" href="(?<url>[^\"]*)"[^>]*>(?<title>.*)<\/a>/siuU', $html, $titleMatch)) {
            return null;
        }
        $entry = new self();
        $entry->titleShortened = RISTools::normalizeTitle($titleMatch['title']);
        $entry->link = $titleMatch['url'];
        $linkParts = explode("/", $entry->link);
        $entry->id = intval($linkParts[count($linkParts) - 1]);

        if (preg_match('/(Gestellt am|Freigabe):<\/div>\s*<div class="keyvalue-value">\s*(?<date>\d+\.\d+\.\d+)\s*<\/div>/siuU', $html, $match)) {
            $entry->gestelltAm = (\DateTime::createFromFormat('d.m.Y', $match['date']))->setTime(0, 0, 0);
        }

        return $entry;
    }
}
