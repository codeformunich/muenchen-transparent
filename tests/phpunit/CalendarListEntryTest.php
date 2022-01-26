<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class CalendarListEntryTest extends TestCase
{
    use MatchesSnapshots;

    public function testParseList()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_index.html');
        $data = CalendarListEntry::parseHtmlList($html);
        $this->assertCount(65, $data);
        $this->assertMatchesObjectSnapshot($data);
    }
}
