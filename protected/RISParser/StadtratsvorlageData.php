<?php

declare(strict_types=1);


class StadtratsvorlageData
{
    public int $id;
    public string $antragsnummer;
    public string $status;
    public string $title;
    public ?string $kurzinfo;
    public bool $hatAntragsliste;
    public string $wahlperiode;
    public ?\DateTime $freigabe;
    public ?string $art;
    public ?string $typ;
    public ?int $referatId;
    public ?string $referatName = null;
    public ?string $referentIn = null;
    public ?int $baNr = null;
    public ?int $baGremiumId = null;
    public ?string $baName = null;

    /** @var DokumentLink[] */
    public array $dokumentLinks;

    /** @var StadtratsantragErgebnis[] */
    public array $ergebnisse;

    /** @var StadtratsantragListEntry[] */
    public array $antraege = [];

    public static function parseFromHtml(string $html, ?int $idFallback): ?self
    {
        if (!preg_match('/<section class="card">.*<div><h2>Betreff<\/h2><\/div>.*<div class="card-body">\s*<div[^>]*>(?<title>[^<]*)<\/div>/siuU', $html, $match)) {
            throw new ParsingException('Not found: title (' . $idFallback . ')');
        }
        $entry = new self();
        $entry->title = html_entity_decode($match['title'], ENT_COMPAT, 'UTF-8');


        if (!preg_match('/<h1[^>]*>.*Sitzungsvorlage (?<nummer>[^<]*)<\/span>\n*<span[^>]*>\n*<span>\((?<status>[^)]*)\)<\/span>/siuU', $html, $match)) {
            throw new ParsingException('Not found: antragsnummer / status (' . $idFallback . ')');
        }
        $entry->antragsnummer = str_replace(' ', '', $match['nummer']);
        $entry->status = $match['status'];

        if (!preg_match('/<a[^>]*href="\.\/(?<id>\d+)\?/siu', $html, $match)) {
            throw new ParsingException('Not found: id (' . $idFallback . ')');
        }
        $entry->id = intval($match['id']);

        if (preg_match('/<section class="card">.*<div><h2>Kurzinformationen<\/h2><\/div>.*<div class="card-body">\s*<div[^>]*>(?<kurzinfo>[^<]*)<\/div>/siuU', $html, $match)) {
            $entry->kurzinfo = html_entity_decode($match['kurzinfo'], ENT_COMPAT, 'UTF-8');
        } else {
            $entry->kurzinfo = null;
        }

        $entry->hatAntragsliste = !!preg_match('/<a [^>]*href="\.\/antraege\//siu', $html);

        if (!preg_match('/<div[^>]*>Wahlperiode:<\/div>\s*<div[^>]*>(?<wahlperiode>\d+-\d+)<\/div>/siuU', $html, $match)) {
            throw new ParsingException('Not found: wahlperiode (' . $idFallback . ')');
        }
        $entry->wahlperiode = $match['wahlperiode'];

        if (preg_match('/<div[^>]*>Freigabe:<\/div>\s*<div[^>]*>(?<date>\d+\.\d+\.\d+)<\/div>/siuU', $html, $match)) {
            $entry->freigabe = (\DateTime::createFromFormat('d.m.Y', $match['date']))->setTime(0, 0, 0);
        } else {
            $entry->freigabe = null;
        }

        if (preg_match('/<div[^>]*>Art:<\/div>\s*<div[^>]*>\s*(<img[^>]*>)?\s*<span>(?<art>[^<]*)<\/span>/siuU', $html, $match)) {
            $entry->art = $match['art'];
        } else {
            $entry->art = null;
        }
        if (preg_match('/<div[^>]*>Typ:<\/div>\s*<div[^>]*>(?<value>[^<]*)<\/div>/siuU', $html, $match)) {
            $entry->typ = trim($match['value']);
            $entry->typ = str_replace('Aenderung', 'Änderung', $entry->typ);
        } else {
            $entry->typ = null;
        }

        if (preg_match('/Zuständiges Referat:<\/div>\s*<div[^>]*>\s*<a[^>]*organisationseinheit\/detail\/(?<id>\d+)"[^>]*>(?<title>[^<]*)<\/a>/siuU', $html, $match)) {
            $entry->referatId = intval($match['id']);
            $entry->referatName = $match['title'];
        }

        if (preg_match('/<div[^>]*>Referent\*in:<\/div>\s*<div[^>]*><span>(?<referentIn>[^<]*)<\/span>/siuU', $html, $match)) {
            $entry->referentIn = $match['referentIn'];
        }

        if (preg_match('/BA-Entscheidung:<\/div>\s*<div[^>]*>\s*<a[^>]*gremium\/detail\/(?<gremiumId>\d+)\?[^\"]+"[^>]*>(?<baNr>\d+) - (?<baName>[^<]*)<\/a>/siuU', $html, $match)) {
            $entry->baNr = intval($match['baNr']);
            $entry->baGremiumId = intval($match['gremiumId']);
            $entry->baName = $match['baName'];
        }

        $entry->dokumentLinks = [];
        $htmlPart = explode('<h2>Ergebnisse</h2>', $html);
        //preg_match_all('/<input[^>]*pdfselector[^>]*pdfid="(?<id>\d+)"[^>]*pdfname="(?<name>[^"]*)"/siu', $htmlPart[0], $matches);
        preg_match_all('/<a class="downloadlink" href="\.\.\/\.\.(?<url>\/dokument\/v\/(?<id>\d+))"[^>]*>(?<filename>(?<title>[^<]*)\.[^<.]*)</siuU', $htmlPart[0], $matches);
        for ($i = 0; $i < count($matches['id']); $i++) {
            $link = new DokumentLink();
            $link->filename = $matches['filename'][$i];
            $link->title = $matches['title'][$i];
            $link->id = intval($matches['id'][$i]);
            $link->url = $matches['url'][$i];
            $entry->dokumentLinks[] = $link;
        }


        $parts = explode('<h2>Ergebnisse</h2>', $html);
        $parts = explode('</section>', $parts[1]);
        preg_match_all('/<a [^>]*href="[^"]*detail\/(?<id>\d+)"[^>]*>(?<date>\d+\.\d+\.\d+)<\/a>/siuU', $parts[0], $matches);
        $entry->ergebnisse = [];
        for ($i = 0; $i < count($matches['id']); $i++) {
            $ergebnis = new StadtratsantragErgebnis();
            $ergebnis->sitzungId = intval($matches['id'][$i]);
            $ergebnis->sitzungAm = (\DateTime::createFromFormat('d.m.Y', $matches['date'][$i]))->setTime(0, 0, 0);
            $entry->ergebnisse[] = $ergebnis;
        }

        return $entry;
    }

    public function parseAntraege(string $html): void
    {
        if (!str_contains($html, 'risi-list')) {
            // @TODO Warning, this should not happen
            return;
        }
        $html = explode('risi-list', $html)[1];
        preg_match_all('/<li.*<\/li>/siuU', $html, $matches);
        $this->antraege = [];
        foreach ($matches[0] as $match) {
            $obj = StadtratsantragListEntry::parseFromHtml($match);
            if ($obj) {
                $this->antraege[] = $obj;
            }
        }
    }
}
