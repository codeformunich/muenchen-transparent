<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class CalendarAgendaItemTest extends TestCase
{
    use MatchesSnapshots;

    public function testParsePublic1()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_AgendaPublic1.html');
        $items = CalendarAgendaItem::parseHtmlList($html);
        $this->assertMatchesObjectSnapshot($items);
    }

    public function testParseNonpublic1()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_AgendaNonpublic1.html');
        $items = CalendarAgendaItem::parseHtmlList($html);
        $this->assertMatchesObjectSnapshot($items);
    }

    public function testParseDecision1()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_AgendaDecision1.html');

        $entry = new CalendarAgendaItem();
        $entry->parseDecision($html);

        $this->assertSame('ohne Entscheidung', $entry->decision);
    }

    public function testParseDisclosure1()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_AgendaDisclosure1.html');

        $entry = new CalendarAgendaItem();
        $entry->parseDisclosure($html);

        $this->assertSame('Der Beschluss unterliegt auf Dauer der Geheimhaltung.', $entry->disclosure);
    }
}
