<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class StadtratsvorlageDataTest extends TestCase
{
    use MatchesSnapshots;

    public function testParse1()
    {
        $html = file_get_contents(__DIR__ . '/data/StadtratsvorlageParser_Dokument1.html');
        $data = StadtratsvorlageData::parseFromHtml($html, 0);
        $this->assertSame("Aufbau eines Referats für Klima- und Umweltschutz und eines Gesundheitsreferats\n- IT-Teil (öffentliche Vorlage)", $data->title);
        $this->assertCount(4, $data->dokumentLinks);
        $this->assertCount(2, $data->ergebnisse);

        $this->assertMatchesObjectSnapshot($data);
    }

    public function testParse2()
    {
        $html = file_get_contents(__DIR__ . '/data/StadtratsvorlageParser_Dokument2.html');
        $data = StadtratsvorlageData::parseFromHtml($html, 0);
        $this->assertSame("Münchner Mietproblematik \"morbus monacensis\"\n\nEmpfehlung Nr. 20-26 / E 00080 ........................", $data->title);
        $this->assertCount(3, $data->dokumentLinks);
        $this->assertCount(1, $data->ergebnisse);

        $this->assertMatchesObjectSnapshot($data);
    }

    public function testParse3()
    {
        $html = file_get_contents(__DIR__ . '/data/StadtratsvorlageParser_Dokument3.html');
        $data = StadtratsvorlageData::parseFromHtml($html, 0);
        $this->assertStringContainsString("Gemäß den Vorschriften der Eigenbetriebsverordnung", $data->title);
        $this->assertCount(7, $data->dokumentLinks);
        $this->assertCount(1, $data->ergebnisse);

        $this->assertMatchesObjectSnapshot($data);
    }

    public function testParseBa()
    {
        $html = file_get_contents(__DIR__ . '/data/StadtratsvorlageParser_DokumentBa.html');
        $data = StadtratsvorlageData::parseFromHtml($html, 0);
        $this->assertStringContainsString('Sanierung der Martin-Behaim-Straße', $data->title);
        $this->assertCount(4, $data->dokumentLinks);
        $this->assertCount(1, $data->ergebnisse);

        $this->assertSame(7, $data->baNr);
        $this->assertSame('Sendling-Westpark', $data->baName);
        $this->assertSame(216, $data->baGremiumId);

        $this->assertMatchesObjectSnapshot($data);
    }

    public function testAntragsliste()
    {
        $html = file_get_contents(__DIR__ . '/data/StadtratsvorlageParser_Antragsliste1.html');
        $data = new StadtratsvorlageData();
        $data->parseAntraege($html);

        $this->assertCount(2, $data->antraege);
        $this->assertSame("Neubau für das Sozialreferat und das Referat für Gesundheit und Umwelt -\nÜbernachtungsschutz fü...", $data->antraege[0]->titleShortened);
        $this->assertSame(6447830, $data->antraege[0]->id);
        $this->assertSame("Neubau für das Sozialreferat und das Referat für Gesundheit und Umwelt -\nÜbernachtungsschutz fü...", $data->antraege[1]->titleShortened);
        $this->assertSame(6447494, $data->antraege[1]->id);
    }
}
