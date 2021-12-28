<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class StadtratsantragDataTest extends TestCase
{
    use MatchesSnapshots;

    public function testParse1()
    {
        $html = file_get_contents(__DIR__ . '/data/StadtratsantragParser_Antrag1.html');
        $data = StadtratsantragData::parseFromHtml($html);
        $this->assertSame('Bericht über die IAA-Mobility 2021', $data->title);
        $this->assertSame('20-26/A02018', $data->antragsnummer);
        $this->assertSame('Erledigt', $data->status);
        $this->assertCount(1, $data->dokumentLinks);
        $this->assertCount(6, $data->ergebnisse);
        $this->assertSame(6805415, $data->ergebnisse[5]->sitzungId);

        $this->assertMatchesObjectSnapshot($data);
    }
}
