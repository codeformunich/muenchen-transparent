<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ReferatDataTest extends TestCase
{
    use MatchesSnapshots;

    public function testParse1()
    {
        $html = file_get_contents(__DIR__ . '/data/Referate_index.html');
        $parts = ReferatData::splitPage($html);
        $this->assertCount(15, $parts);

        $ref = ReferatData::parseFromHtml($parts[3]);
        $this->assertSame('IT-Referat', $ref->name);
        $this->assertSame(4769009, $ref->id);
        $this->assertSame(4873150, $ref->referentInId);
        $this->assertSame('Thomas Bönig', $ref->referentInName);
    }
}
