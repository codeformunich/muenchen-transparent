<?php

declare(strict_types=1);

class CalendarListEntry
{
    public ?\DateTime $dateStart;
    public int $id;
    public string $status;
    public int $organizationId;
    public string $organizationName;

    /**
     * @return CalendarListEntry[]
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
        if (!preg_match('/<a class="headline-link[^>]+ href="(?<url>[^\"]*)(;[^\"]*)?"[^>]*>(?<title>.*)<\/a>/siuU', $html, $match)) {
            return null;
        }

        $entry = new self();
        $linkParts = explode('/', $match['url']);
        $entry->id = intval(array_pop($linkParts));
        $entry->dateStart = RISParser::parseGermanLongDate($match['title']);

        preg_match('/<a class="icon-action" href="\.\/gremium\/detail\/(?<id>\d+)\?[^>]*>(?<title>[^<]*)<\/a>/siu', $html, $match);
        $entry->organizationId = intval($match['id']);
        $entry->organizationName = $match['title'];

        return $entry;
    }
}
