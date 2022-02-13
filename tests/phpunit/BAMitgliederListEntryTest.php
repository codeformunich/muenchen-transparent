<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class BAMitgliederListEntryTest extends TestCase
{
    use MatchesSnapshots;

    public function testParseList()
    {
        $html = file_get_contents(__DIR__ . '/data/BAMitglieder_Index.html');
        $data = BAMitgliedListEntry::parseHtmlList($html);
        $this->assertCount(33, $data);
        $this->assertMatchesObjectSnapshot($data);
    }
}
