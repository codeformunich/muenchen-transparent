<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class AntragDataTest extends TestCase
{
    use MatchesSnapshots;

    public function testParseStadtrat(): void
    {
        $html = file_get_contents(__DIR__ . '/data/AntragParser_Stadtrat1.html');
        $data = AntragData::parseFromHtml($html);
        $this->assertSame('Bericht über die IAA-Mobility 2021', $data->title);
        $this->assertSame('20-26/A02018', $data->antragsnummer);
        $this->assertSame('Erledigt', $data->status);
        $this->assertCount(1, $data->dokumentLinks);
        $this->assertCount(6, $data->ergebnisse);
        $this->assertSame(6805415, $data->ergebnisse[5]->sitzungId);

        $this->assertMatchesObjectSnapshot($data);
    }

    public function testParseBA(): void
    {
        $html = file_get_contents(__DIR__ . '/data/AntragParser_BA1.html');
        $data = AntragData::parseFromHtml($html);
        $this->assertSame('Umwandlung Pkw-Parkplatz zur Fahrradabstellfläche vor Kinderarztpraxis in der Kolumbusstr. 11', $data->title);
        $this->assertSame(5, $data->baNr);
        $this->assertSame(214, $data->baId);
        $this->assertCount(3, $data->dokumentLinks);
        $this->assertCount(1, $data->ergebnisse);

        $this->assertMatchesObjectSnapshot($data);
    }
}
