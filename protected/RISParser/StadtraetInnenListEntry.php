<?php

declare(strict_types=1);

class StadtraetInnenListEntry
{
    public int $id;
    public string $link;
    public string $name;
    public ?int $fraktionId = null;
    public ?string $fraktionName = null;

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
        $entry->name = preg_replace('/^(Herr|Frau) /siu', '', $titleMatch['title']);
        $entry->link = $titleMatch['url'];
        $linkParts = explode("/", $entry->link);
        $entry->id = intval($linkParts[count($linkParts) - 1]);

        if (preg_match('/<a class="icon_action" href="\.\.\/gremium\/detail\/(?<id>\d+);[^>]+>(?<titel>[^<]*)<\/a>/siu', $html, $match)) {
            $entry->fraktionId = intval($match['id']);
            $entry->fraktionName = trim($match['titel']);
        }

        return $entry;
    }
}
