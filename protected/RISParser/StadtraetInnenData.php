<?php

declare(strict_types=1);

class StadtraetInnenData
{
    public int $id;
    public string $name;
    public ?\DateTime $gewaehltAm = null;
    public ?\DateTime $mandatSeit = null;
    public ?string $lebenslauf = null;
    public ?string $fotoUrl = null;

    /** @var StadtraetInnenGremienmitgliedschaftData[] */
    public array $fraktionsMitgliedschaften;

    /** @var StadtraetInnenGremienmitgliedschaftData[] */
    public array $ausschussMitgliedschaften;

    public static function parseFromHtml(string $htmlFraktion, string $htmlAusschuss): ?self
    {
        if (!preg_match('/<h1 class="page-title">\n<span[^>]*>(?<name>[^<]*) <span/siuU', $htmlFraktion, $match)) {
            throw new ParsingException('Not found: name');
        }
        $entry = new self();
        $entry->name = preg_replace('/^(Herr|Frau) /siu', '', $match['name']);

        if (!preg_match('/rss\?feed=StR\-MitgliedAntraege&amp;personid=(?<id>\d+)"/siu', $htmlFraktion, $match)) {
            throw new ParsingException('Not found: id');
        }
        $entry->id = intval($match['id']);

        if (preg_match('/Gewählt am:<\/div>\s*<div class="keyvalue-value">\s*(?<date>\d+\.\d+\.\d+)\s*<\/div>/siuU', $htmlFraktion, $match)) {
            $entry->gewaehltAm = (\DateTime::createFromFormat('d.m.Y', $match['date']))->setTime(0, 0, 0);
        }
        if (preg_match('/Mandat seit:<\/div>\s*<div class="keyvalue-value">\s*(?<date>\d+\.\d+\.\d+)\s*<\/div>/siuU', $htmlFraktion, $match)) {
            $entry->mandatSeit = (\DateTime::createFromFormat('d.m.Y', $match['date']))->setTime(0, 0, 0);
        }

        if (preg_match('/Lebenslauf:<\/div>\s*<div class="keyvalue-value">(?<bio>.*)<\/div>/siuU', $htmlFraktion, $match)) {
            $entry->lebenslauf = $match['bio'];
        }

        if (preg_match('/<img [^>]*src="\.\.\/\.\.\/bild\/(?<id>\d+)">/siu', $htmlFraktion, $match)) {
            $entry->fotoUrl = 'https://risi.muenchen.de/risi/bild/' . $match['id'];
        }

        $fraktionList = explode('risi-list', $htmlFraktion)[1];
        preg_match_all('/<li.*<\/li>/siuU', $fraktionList, $matches);
        $entry->fraktionsMitgliedschaften = [];
        foreach ($matches[0] as $match) {
            $entry->fraktionsMitgliedschaften[] = StadtraetInnenGremienmitgliedschaftData::parseFromHtml($match);
        }

        // @TODO This is only the first page with the most recent memberships
        $ausschussList = explode('risi-list', $htmlAusschuss)[1];
        preg_match_all('/<li.*<\/li>/siuU', $ausschussList, $matches);
        $entry->ausschussMitgliedschaften = [];
        foreach ($matches[0] as $match) {
            $entry->ausschussMitgliedschaften[] = StadtraetInnenGremienmitgliedschaftData::parseFromHtml($match);
        }


        return $entry;
    }
}
