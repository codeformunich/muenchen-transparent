<?php

declare(strict_types=1);

class CalendarAgendaItem
{
    public int $position;
    public ?int $id;
    public bool $public;
    public bool $isHeading;
    public string $topNr;
    public string $title;
    public bool $hasDisclosure = false;
    public ?string $disclosure = null; // Only for non-public agenda items
    public bool $hasDecision = false;
    public ?string $decision = null; // For public agenda items
    public ?DokumentLink $decisionDocument = null;

    /** @var int[] */
    public array $antraegeIds = [];

    /** @var int[] */
    public array $vorlagenIds = [];

    /**
     * @return CalendarAgendaItem[]
     */
    public static function parseHtmlList(string $html, bool $public): array
    {
        $parts = explode('d-table w-100 tops', $html);
        $entries = preg_split('/<div class="d-table-row (odd|even)/siu', $parts[1]);
        array_shift($entries);

        $parsedObjects = [];
        $topContext = '';
        $pos = 0;
        foreach ($entries as $entry) {
            $parsed = static::parseFromHtml($entry, $pos, $public, $topContext);
            if ($parsed) {
                $parsedObjects[] = $parsed;
                $pos++;

                if ($parsed->isHeading) {
                    $topContext = $parsed->topNr;
                }
            }
        }

        return $parsedObjects;
    }

    public static function parseFromHtml(string $html, int $pos, bool $public, string $lastHeadingTopNr): ?self
    {
        $entry = new self();
        $entry->position = $pos;
        $entry->public = $public;
        $entry->isHeading = (str_contains($html, 'topabschnitt') || str_contains($html, 'topueberschrift'));

        if (!preg_match('/<div class="d-table-cell px-1 py-2 text-center">\s*<span>(?<topNr>[^<]*)<\/span>/siu', $html, $match)) {
            throw new ParsingException('Not found: topNr');
        }
        if ($entry->isHeading || $lastHeadingTopNr === '') {
            $entry->topNr = $match['topNr'];
        } else {
            $entry->topNr = $lastHeadingTopNr . (str_ends_with($lastHeadingTopNr, '.') ? '' : '.') . $match['topNr'];
        }

        if (preg_match('/topdownload\?risid=(?<id>\d+)[^\d]/siu', $html, $match)) {
            $entry->id = intval($match['id']);
        } elseif (preg_match('/\.\.\/top\/(?<id>\d+)\//siu', $html, $match)) {
            $entry->id = intval($match['id']);
        } else {
            $entry->id = null;
        }

        if (!preg_match('/<div class="d-flex justify-content-between">\s*<div><span class="text-keepwhitespace">(?<title>[^<]*)<\/span>/siu', $html, $match)) {
            throw new ParsingException('Not found: title');
        }
        $entry->title = html_entity_decode($match['title'], ENT_COMPAT, 'UTF-8');

        if (preg_match('/top\/' . $entry->id . '\/entscheidung/siu', $html)) {
            $entry->hasDecision = true;
        }
        if (preg_match('/top\/' . $entry->id . '\/veroeffentlichung/siu', $html)) {
            $entry->hasDisclosure = true;
        }

        if (preg_match('/<a class="downloadlink" href="[.\/]*(?<url>\/dokument\/v\/(?<id>\d+))"[^>]*>(?<filename>(?<title>[^<]*)\.[^<.]*)</siuU', $html, $match)) {
            $entry->decisionDocument = new DokumentLink();
            $entry->decisionDocument->filename = $match['filename'];
            $entry->decisionDocument->title = $match['title'];
            $entry->decisionDocument->id = intval($match['id']);
            $entry->decisionDocument->url = $match['url'];
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
