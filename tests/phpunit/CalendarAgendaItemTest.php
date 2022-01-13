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
        $items = CalendarAgendaItem::parseHtmlList($html, true);
        $this->assertMatchesObjectSnapshot($items);
    }

    public function testParsePublic2()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_AgendaPublic2.html');
        $items = CalendarAgendaItem::parseHtmlList($html, true);
        $this->assertCount(13, $items);

        $this->assertStringStartsWith('Karstadt am Nordbad', $items[1]->title);
        $this->assertTrue($items[1]->hasDecision);
        $this->assertSame(6878435, $items[1]->decisionDocument->id);

        $this->assertMatchesObjectSnapshot($items);
    }

    public function testParseNonpublic1()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_AgendaNonpublic1.html');
        $items = CalendarAgendaItem::parseHtmlList($html, false);
        $this->assertMatchesObjectSnapshot($items);
    }

    public function testParsePublicBA1()
    {
        $html = file_get_contents(__DIR__ . '/data/CalendarParser_BA_Agenda1.html');
        $items = CalendarAgendaItem::parseHtmlList($html, true);
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
