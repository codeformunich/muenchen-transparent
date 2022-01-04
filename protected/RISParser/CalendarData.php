<?php

declare(strict_types=1);

class CalendarData
{
    public ?\DateTime $dateStart;
    public int $id;
    public string $status;
    public ?string $sitzungsstand = null;
    public int $organizationId;
    public string $organizationName;
    public string $wahlperiode;
    public ?string $ort = null;
    public ?int $vorsitzId = null;
    public ?string $vorsitzName = null;
    public ?int $referatId = null;
    public ?string $referatName = null;
    public ?string $referentInName = null;

    public bool $hasAgendaPublic;
    public bool $hasAgendaNonPublic;

    /** @var DokumentLink[] */
    public array $dokumentLinks;

    /** @var CalendarAgendaItem[] */
    public array $agendaPublic = [];

    /** @var CalendarAgendaItem[] */
    public array $agendaNonPublic = [];

    public static function parseFromHtml(string $html, int $id): ?self
    {
        if (!preg_match('/<h1[^>]*>\s*<span[^>]*>(?<date>[^<]*)<span[^>]*><span[^>]*>(?<status>[^>]*)<\/span>/siuU', $html, $match)) {
            throw new ParsingException('Not found: date/status');
        }
        $entry = new self();
        $entry->dateStart = RISParser::parseGermanLongDate($match['date']);
        $entry->status = trim($match['status'], " \t\n\r\0\x0B()");
        if (str_contains($match['date'], 'entfällt')) {
            $entry->sitzungsstand = Termin::CANCELED_STR;
            // For canceled events, the ID is not part of the HTML
            $entry->id = $id;
        } else {
            if (!preg_match('/rss[^"]*sitzungid=(?<id>\d+)[^\d]/siu', $html, $match)) {
                throw new ParsingException('Not found: id');
            }
            $entry->id = intval($match['id']);
        }

        if (preg_match('/Gremium:<\/div>\s*<div[^>]*>\s*<a[^>]*gremium\/detail\/(?<id>\d+)[^\d][^>]*>(?<title>[^<]*)<\/a>/siuU', $html, $match)) {
            $entry->organizationId = intval($match['id']);
            $entry->organizationName = $match['title'];
        }
        if (preg_match('/Zuständiges Referat:<\/div>\s*<div[^>]*>\s*<a[^>]*organisationseinheit\/detail\/(?<id>\d+)"[^>]*>(?<title>[^<]*)<\/a>/siuU', $html, $match)) {
            $entry->referatId = intval($match['id']);
            $entry->referatName = $match['title'];
        }
        if (preg_match('/<div[^>]*>Referent\*in:<\/div>\s*<div[^>]*><span>(?<referentIn>[^<]*)<\/span>/siuU', $html, $match)) {
            $entry->referentInName = $match['referentIn'];
        }

        if (!preg_match('/<div[^>]*>Wahlperiode:<\/div>\s*<div[^>]*>(?<wahlperiode>\d+-\d+)<\/div>/siuU', $html, $match)) {
            throw new ParsingException('Not found: wahlperiode');
        }
        $entry->wahlperiode = $match['wahlperiode'];

        if (preg_match('/<div[^>]*>Sitzungsort:<\/div>\s*<div[^>]*>(?<ort>[^<]*)<\/div>/siuU', $html, $match)) {
            $entry->ort = $match['ort'];
        }

        if (preg_match('/<div[^>]*>Vorsitz:<\/div>\s*<div[^>]*>(?<vorsitzHtml>.*)<\/div>/siuU', $html, $match)) {
            preg_match_all('/<a [^>]*href="\.\.\/\.\.\/person\/detail\/(?<id>\d+)[^\d][^>]*>(?<name>[^<]*)<\/a>/siu', $match['vorsitzHtml'], $matches);
            if (isset($matches['id']) && count($matches['id']) > 0) {
                $entry->vorsitzId = intval($matches['id'][0]);
                $entry->vorsitzName = $matches['name'][0];
            }
            if (isset($matches['id']) && count($matches['id']) > 1) {
                echo "WARNING: More than one \"Vorsitz\" found, skipping all but the first one\n";
            }
        }

        $entry->hasAgendaPublic = str_contains($html, $entry->id . '/tagesordnung/oeffentlich');
        $entry->hasAgendaNonPublic = str_contains($html, $entry->id . '/tagesordnung/nichtoeffentlich');

        $entry->dokumentLinks = [];
        $htmlPart = explode('<h2>Dokumente</h2>', $html);
        if (count($htmlPart) > 1) {
            preg_match_all('/<a class="downloadlink" href="\.\.\/\.\.(?<url>\/dokument\/v\/(?<id>\d+))"[^>]*>(?<filename>(?<title>[^<]*)\.[^<.]*)</siuU', $htmlPart[1], $matches);
            for ($i = 0; $i < count($matches['id']); $i++) {
                $link = new DokumentLink();
                $link->filename = $matches['filename'][$i];
                $link->title = $matches['title'][$i];
                $link->id = intval($matches['id'][$i]);
                $link->url = $matches['url'][$i];
                $entry->dokumentLinks[] = $link;
            }
        }

        return $entry;
    }

    public function parseAgendaPublic(string $agenda): void
    {
        $this->agendaPublic = CalendarAgendaItem::parseHtmlList($agenda, true);
    }

    public function parseAgendaNonPublic(string $agenda): void
    {
        $this->agendaNonPublic = CalendarAgendaItem::parseHtmlList($agenda, false);
    }
}
