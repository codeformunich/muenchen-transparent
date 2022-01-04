<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class CalendarDataTest extends TestCase
{
    use MatchesSnapshots;

    public function testParseDate1()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_Data1.html');
        $data = CalendarData::parseFromHtml($html, 6431692);

        $this->assertSame(6431692, $data->id);
        $this->assertSame('Ausschuss für Stadtplanung und Bauordnung', $data->organizationName);
        $this->assertMatchesObjectSnapshot($data);
    }

    public function testParseDate2()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_Data2.html');
        $data = CalendarData::parseFromHtml($html, 6431930);

        $this->assertSame(6431930, $data->id);
        $this->assertSame('Ausschuss für Klima- und Umweltschutz', $data->organizationName);
        $this->assertMatchesObjectSnapshot($data);
    }

    public function testParseBA1()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_BA_Data1.html');
        $data = CalendarData::parseFromHtml($html, 6243091);

        $this->assertSame(6243091, $data->id);
        $this->assertSame('BA 14 - Vollgremium', $data->organizationName);
        //$this->assertSame(14, $data->o); @TODO BA
        $this->assertMatchesObjectSnapshot($data);
    }
}
