<?php

declare(strict_types=1);

class StadtraetInnenGremienmitgliedschaftData
{
    public int $gremiumId;
    public string $gremiumName;
    public ?string $funktion = null;
    public ?DateTime $seit = null;
    public ?DateTime $bis = null;
    public ?int $wahlperiode = null;

    public static function parseFromHtml(string $html): ?self
    {
        $entry = new self();

        if (!preg_match('/<a class="headline-link[^>]*href="\.\.\/\.\.\/gremium\/detail\/(?<id>\d+)[^\d][^>]*>(?<name>[^<]*)<\/a>/siuU', $html, $matches)) {
            throw new ParsingException('Not found: title');
        }
        $entry->gremiumId = intval($matches['id']);
        $entry->gremiumName = $matches['name'];

        if (preg_match('/Zugehörigkeit:<\/div>\s*<div class="keyvalue-value">\s*' .
                        '<div>(von|seit) (?<seit>\d+\.\d+\.\d+)( bis (?<bis>\d+\.\d+\.\d+))?\s*<\/div>/siuU', $html, $matches)) {
            $entry->seit = (\DateTime::createFromFormat('d.m.Y', $matches['seit']))->setTime(0, 0, 0);
            if (isset($matches['bis'])) {
                $entry->bis = (\DateTime::createFromFormat('d.m.Y', $matches['bis']))->setTime(0, 0, 0);
            }
        }

        if (preg_match('/Funktion:<\/div>\s*<div class="keyvalue-value">\s*<div>(?<funktion>[^<]*)\s*<\/div>/siuU', $html, $matches)) {
            $entry->funktion = $matches['funktion'];
        }

        if (preg_match('/Wahlperiode:<\/div>\s*<div class="keyvalue-value">\s*(?<wahlperiode>[^<]*)\s*<\/div>/siuU', $html, $matches)) {
            $entry->wahlperiode = Wahlperioden::WAHLPERIODEN_BY_YEAR[intval($matches['wahlperiode'])];
        }

        return $entry;
    }
}
