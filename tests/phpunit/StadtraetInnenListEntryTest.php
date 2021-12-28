<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class StadtraetInnenListEntryTest extends TestCase
{
    use MatchesSnapshots;

    public function testParseList()
    {
        $html = file_get_contents(__DIR__ . '/data/StadtraetInnen_index.html');
        $data = StadtraetInnenListEntry::parseHtmlList($html);
        $this->assertCount(6, $data);
        $this->assertMatchesObjectSnapshot($data);
    }
}
