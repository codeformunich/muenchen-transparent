<?php

declare(strict_types=1);

class AntragData
{
    public int $id;
    public string $antragsnummer;
    public string $status;
    public ?int $baNr = null;
    public ?int $baId = null;
    public string $title;
    public string $wahlperiode;
    public ?\DateTime $gestelltAm;
    public ?\DateTime $registriertAm;
    public ?\DateTime $bearbeitungsfrist;
    public ?\DateTime $erledigtAm;
    public ?string $art;
    public ?string $typ;
    public ?string $bearbeitungsart;
    public ?int $referatId = null;
    public ?string $referatName = null;

    /** @var string[] */
    public array $initiativeNamen;

    /** @var string[] */
    public array $gestelltVon;

    /** @var DokumentLink[] */
    public array $dokumentLinks;

    /** @var StadtratsantragErgebnis[] */
    public array $ergebnisse;

    public static function parseFromHtml(string $html): ?self
    {
        if (!preg_match('/<section class="card">.*<div><h2>Betreff<\/h2><\/div>.*<div class="card-body">\s*<div[^>]*>(?<title>[^<]*)<\/div>/siuU', $html, $match)) {
            throw new ParsingException('Not found: title');
        }
        $entry = new self();
        $entry->title = $match['title'];

        if (!preg_match('/<h1[^>]*>.*(StR|BA)-(Antrag|Anfrage) (?<nummer>[^<]*) <span[^>]*><span>\((?<status>[^)]*)\)<\/span>/siuU', $html, $match)) {
            throw new ParsingException('Not found: antragsnummer / status');
        }
        $entry->antragsnummer = str_replace(' ', '', $match['nummer']);
        $entry->status = $match['status'];

        if (!preg_match('/<a[^>]*href="\.\/(?<id>\d+)\?/siu', $html, $match)) {
            throw new ParsingException('Not found: id');
        }
        $entry->id = intval($match['id']);

        if (!preg_match('/<div[^>]*>Wahlperiode:<\/div>\s*<div[^>]*>(?<wahlperiode>\d+-\d+)<\/div>/siuU', $html, $match)) {
            throw new ParsingException('Not found: wahlperiode');
        }
        $entry->wahlperiode = $match['wahlperiode'];

        if (preg_match('/Bezirksausschuss<\/span>:<\/div>\s*<div[^>]*>\s*<a[^>]*gremium\/detail\/(?<baId>\d+)[^\d][^>]*>(?<baNr>\d+ -)/siuU', $html, $match)) {
            $entry->baId = intval($match['baId']);
            $entry->baNr = intval($match['baNr']);
        }

        if (preg_match('/<div[^>]*>Gestellt am:<\/div>\s*<div[^>]*>(?<date>\d+\.\d+\.\d+)<\/div>/siuU', $html, $match)) {
            $entry->gestelltAm = (\DateTime::createFromFormat('d.m.Y', $match['date']))->setTime(0, 0, 0);
        } else {
            $entry->gestelltAm = null;
        }
        if (preg_match('/<div[^>]*>Registriert am:<\/div>\s*<div[^>]*>(?<date>\d+\.\d+\.\d+)<\/div>/siuU', $html, $match)) {
            $entry->registriertAm = (\DateTime::createFromFormat('d.m.Y', $match['date']))->setTime(0, 0, 0);
        } else {
            $entry->registriertAm = null;
        }
        if (preg_match('/<div[^>]*>Bearbeitungs-Frist:<\/div>\s*<div[^>]*>(?<date>\d+\.\d+\.\d+)<\/div>/siuU', $html, $match)) {
            $entry->bearbeitungsfrist = (\DateTime::createFromFormat('d.m.Y', $match['date']))->setTime(0, 0, 0);
        } else {
            $entry->bearbeitungsfrist = null;
        }
        if (preg_match('/<div[^>]*>Erledigt am:<\/div>\s*<div[^>]*>(?<date>\d+\.\d+\.\d+)<\/div>/siuU', $html, $match)) {
            $entry->erledigtAm = (\DateTime::createFromFormat('d.m.Y', $match['date']))->setTime(0, 0, 0);
        } else {
            $entry->erledigtAm = null;
        }

        if (preg_match('/<div[^>]*>Art:<\/div>\s*<div[^>]*>\s*(<img[^>]*>)?\s*<span>(?<art>[^<]*)<\/span>/siuU', $html, $match)) {
            $entry->art = $match['art'];
        } else {
            $entry->art = null;
        }
        if (preg_match('/<div[^>]*>Bearbeitungs-Art:<\/div>\s*<div[^>]*>(?<value>[^<]*)<\/div>/siuU', $html, $match)) {
            $entry->bearbeitungsart = trim($match['value']);
        } else {
            $entry->bearbeitungsart = null;
        }
        if (preg_match('/<div[^>]*>Typ:<\/div>\s*<div[^>]*>(?<value>[^<]*)<\/div>/siuU', $html, $match)) {
            $entry->typ = trim($match['value']);
            $entry->typ = str_replace('Aenderung', 'Änderung', $entry->typ);
        } else {
            $entry->typ = null;
        }


        if (preg_match('/<div[^>]*>Gestellt von:<\/div>\s*<div[^>]*>(?<value>[^<]*)<\/div>/siuU', $html, $match)) {
            $entry->gestelltVon = array_filter(array_map('trim', explode(',', $match['value'])));
        } else {
            $entry->gestelltVon = [];
        }
        if (preg_match('/<div[^>]*>Initiative:<\/div>\s*<div[^>]*>(?<value>[^<]*)<\/div>/siuU', $html, $match)) {
            $entry->initiativeNamen = array_filter(array_map('trim', explode(',', $match['value'])));
        } else {
            $entry->initiativeNamen = [];
        }

        if (preg_match('/Zuständiges Referat:<\/div>\s*<div[^>]*>\s*<a[^>]*organisationseinheit\/detail\/(?<id>\d+)"[^>]*>(?<title>[^<]*)<\/a>/siuU', $html, $match)) {
            $entry->referatId = intval($match['id']);
            $entry->referatName = $match['title'];
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
}
