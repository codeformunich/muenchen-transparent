<?php

declare(strict_types=1);

class CalendarListEntry
{
    public ?\DateTime $dateStart;
    public int $id;
    public string $link;
    public int $organizationId;
    public string $organizationName;

    private const MONTHS = [
        "januar" => 1,
        "februar" => 2,
        "märz" => 3,
        "april" => 4,
        "mai" => 5,
        "juni" => 6,
        "juli" => 7,
        "august" => 8,
        "september" => 9,
        "oktober" => 10,
        "november" => 11,
        "dezember" => 12,
    ];

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
        $entry->link = $match['url'];
        $linkParts = explode('/', $match['url']);
        $entry->id = intval(array_pop($linkParts));

        preg_match('/(?<day>\d+)\. (?<month>[a-zäöüß]+) (?<year>\d{4}), (?<hour>\d+):(?<minute>\d+)/siu', $match['title'], $match);
        $entry->dateStart = (new \DateTime())
            ->setDate(intval($match['year']), static::MONTHS[mb_strtolower($match['month'])], intval($match['day']))
            ->setTime(intval($match['hour']), intval($match['minute']), 0, 0);

        preg_match('/<a class="icon_action" href="\.\/gremium\/detail\/(?<id>\d+)\?[^>]*>(?<title>[^<]*)<\/a>/siu', $html, $match);
        $entry->organizationId = intval($match['id']);
        $entry->organizationName = $match['title'];

        return $entry;
    }
}
