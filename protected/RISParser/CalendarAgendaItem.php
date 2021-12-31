<?php

declare(strict_types=1);

class CalendarAgendaItem
{
    public int $id;
    public string $topNr;
    public string $title;
    public bool $hasDisclosure = false;
    public ?string $disclosure = null; // Only for non-public agenda items
    public bool $hasDecision = false;
    public ?string $decision = null; // For public agenda items

    /** @var int[] */
    public array $antraegeIds = [];

    /** @var int[] */
    public array $vorlagenIds = [];

    /**
     * @return CalendarAgendaItem[]
     */
    public static function parseHtmlList(string $html): array
    {
        $parts = explode('d-table-row even topueberschrift', $html);
        $entries = preg_split('/<div class="d-table-row (odd|even)">/siu', $parts[1]);
        array_shift($entries);

        $parsedObjects = [];
        foreach ($entries as $entry) {
            $parsed = static::parseFromHtml($entry);
            if ($parsed) {
                $parsedObjects[] = $parsed;
            }
        }

        return $parsedObjects;
    }

    public static function parseFromHtml(string $html): ?self
    {
        $entry = new self();

        if (!preg_match('/<div class="d-table-cell px-1 py-2 text-center">\s*<span>(?<topNr>[^<]*)<\/span>/siu', $html, $match)) {
            throw new ParsingException('Not found: topNr');
        }
        $entry->topNr = $match['topNr'];

        if (preg_match('/topdownload?risid=(?<id>\d+)[^\d]/siu', $html, $match)) {
            $entry->id = intval($match['id']);
        } elseif (preg_match('/\.\.\/top\/(?<id>\d+)\//siu', $html, $match)) {
            $entry->id = intval($match['id']);
        } else {
            throw new ParsingException('No id found');
        }

        if (!preg_match('/<div class="d-flex justify-content-between">\s*<div><span class="text-keepwhitespace">(?<title>[^<]*)<\/span>/siu', $html, $match)) {
            throw new ParsingException('Not found: title');
        }
        $entry->title = $match['title'];

        if (preg_match('/top\/' . $entry->id . '\/entscheidung/siu', $html)) {
            $entry->hasDecision = true;
        }
        if (preg_match('/top\/' . $entry->id . '\/veroeffentlichung/siu', $html)) {
            $entry->hasDisclosure = true;
        }

        preg_match_all('/antrag\/detail\/(?<id>\d+)[^\d]/siu', $html, $matches);
        $entry->antraegeIds = array_map('intval', $matches['id'] ?? []);

        preg_match_all('/sitzungsvorlage\/detail\/(?<id>\d+)[^\d]/siu', $html, $matches);
        $entry->vorlagenIds = array_map('intval', $matches['id'] ?? []);

        return $entry;
    }

    public function parseDecision(string $html): void
    {
        if (!preg_match('/Entscheidung:<\/div>\s*<div class="keyvalue-value"><span>(?<str>[^<]*)<\/span>/siu', $html, $matches)) {
            throw new ParsingException('Not found: decision');
        }
        $this->decision = $matches['str'];
    }

    public function parseDisclosure(string $html): void
    {
        if (!preg_match('/<h2>Bekanntgabe.*<div class="card-body">\s<span>(?<str>[^<]*)<\/span>/siu', $html, $matches)) {
            throw new ParsingException('Not found: disclosure');
        }
        $this->disclosure = $matches['str'];
    }
}
