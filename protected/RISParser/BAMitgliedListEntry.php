<?php

declare(strict_types=1);

class BAMitgliedListEntry
{
    public int $id;
    public string $link;
    public string $name;
    public ?int $baNr = null;
    public ?string $baName = null;

    /**
     * @return StadtraetInnenListEntry[]
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

    public static function parseFromHtml(string $html): ?static
    {
        if (!preg_match('/<a class="headline-link[^>]+ href="(?<url>[^\"]*)(;[^\"]*)?"[^>]*>(?<title>.*)<\/a>/siuU', $html, $titleMatch)) {
            return null;
        }
        $entry = new self();
        if (preg_match('/<a class="icon_action"[^>]*>(?<baNo>\d+) \- (?<baName>[^<]*)<\/a>/siu', $html, $match)) {
            $entry->baName = $match['baName'];
            $entry->baNr = intval($match['baNo']);
        } else {
            return null;
        }
        $entry->name = preg_replace('/^(Herr|Frau) /siu', '', $titleMatch['title']);
        $entry->link = $titleMatch['url'];
        $linkParts = explode("/", $entry->link);
        $entry->id = intval($linkParts[count($linkParts) - 1]);


        return $entry;
    }
}
