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
        $data = StadtratsvorlageData::parseFromHtml($html);
        $this->assertSame("Aufbau eines Referats für Klima- und Umweltschutz und eines Gesundheitsreferats\n- IT-Teil (öffentliche Vorlage)", $data->title);
        $this->assertCount(4, $data->dokumentLinks);
        $this->assertCount(2, $data->ergebnisse);

        $this->assertMatchesObjectSnapshot($data);
    }

    public function testParseBa()
    {
        $html = file_get_contents(__DIR__ . '/data/StadtratsvorlageParser_DokumentBa.html');
        $data = StadtratsvorlageData::parseFromHtml($html);
        $this->assertStringContainsString('Sanierung der Martin-Behaim-Straße', $data->title);
        $this->assertCount(4, $data->dokumentLinks);
        $this->assertCount(1, $data->ergebnisse);

        $this->assertSame(7, $data->baNr);
        $this->assertSame('Sendling-Westpark', $data->baName);
        $this->assertSame(216, $data->baGremiumId);

        $this->assertMatchesObjectSnapshot($data);
    }
}
