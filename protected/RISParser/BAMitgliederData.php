<?php

declare(strict_types=1);

class BAMitgliederData
{
    public int $id;
    public string $name;
    public int $baNr;

    /** @var GremienmitgliedschaftData[] */
    public array $fraktionsMitgliedschaften;

    /** @var GremienmitgliedschaftData[] */
    public array $baMitgliedschaften;

    /** @var GremienmitgliedschaftData[] */
    public array $baAusschuesse;

    public static function parseFromHtml(string $htmlBa, string $htmlFraktion, string $htmlAusschuesse, ?int $idFallback): ?self
    {
        if (!preg_match('/<h1 class="page-title">\s*\n\s*<span[^>]*>(?<name>[^<]*) <span/siuU', $htmlBa, $match)) {
            throw new ParsingException('Not found: name (' . $idFallback . ')');
        }
        $entry = new self();
        $entry->name = preg_replace('/^(Herr|Frau) /siu', '', $match['name']);

        if (!preg_match('/person\/detail\/(?<id>\d+)\?/siu', $htmlBa, $match)) {
            throw new ParsingException('Not found: id (' . $idFallback . ')');
        }
        $entry->id = intval($match['id']);

        $fraktionList = explode('risi-list', $htmlFraktion)[1];
        $entry->fraktionsMitgliedschaften = [];
        if (!str_contains($fraktionList, 'Es wurden keine Einträge gefunden')) {
            preg_match_all('/<li.*<\/li>/siuU', $fraktionList, $matches);
            foreach ($matches[0] as $match) {
                $entry->fraktionsMitgliedschaften[] = GremienmitgliedschaftData::parseFromHtml($match);
            }
        }

        $baList = explode('risi-list', $htmlBa)[1];
        preg_match_all('/<li.*<\/li>/siuU', $baList, $matches);
        $entry->baMitgliedschaften = [];
        foreach ($matches[0] as $match) {
            try {
                $mitgliedschaft = GremienmitgliedschaftData::parseFromHtml($match);
                $entry->baMitgliedschaften[] = $mitgliedschaft;
                if ($mitgliedschaft->baNr) {
                    $entry->baNr = $mitgliedschaft->baNr;
                }
            } catch (ParsingException $e) {
                echo "Could not parse BA-Mitgliedschaft: " . $e->getMessage() . "\n";
            }
        }

        $ausschussList = explode('risi-list', $htmlAusschuesse)[1];
        preg_match_all('/<li.*<\/li>/siuU', $ausschussList, $matches);
        $entry->baAusschuesse = [];
        foreach ($matches[0] as $match) {
            $entry->baAusschuesse[] = GremienmitgliedschaftData::parseFromHtml($match);
        }

        return $entry;
    }
}
