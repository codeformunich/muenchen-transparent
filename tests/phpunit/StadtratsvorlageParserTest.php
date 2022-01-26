<?php

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StadtratsvorlageParserTest extends TestCase
{
    /** @var BrowserBasedDowloader|MockObject */
    private $browserBasedDownloader;

    /** @var CurlBasedDownloader|MockObject */
    private $curlBasedDownloader;

    /** @var StadtratsantragParser|MockObject */
    private $stadtratsantragParser;

    private ?StadtratsvorlageParser $parser = null;

    public function setUp(): void
    {
        $this->browserBasedDownloader = $this->createMock(BrowserBasedDowloader::class);
        $this->curlBasedDownloader = $this->createMock(CurlBasedDownloader::class);
        $this->stadtratsantragParser = $this->createMock(StadtratsantragParser::class);
        $this->parser = new StadtratsvorlageParser($this->browserBasedDownloader, $this->curlBasedDownloader);
        $this->parser->setStadtratsantragParser($this->stadtratsantragParser);
        CurlBasedDownloader::setInstance($this->curlBasedDownloader);
    }


    public function testParseMonth()
    {
        $this->browserBasedDownloader
            ->method('downloadDocumentTypeListForPeriod')
            ->willReturn(file_get_contents(__DIR__ . '/data/StadtratsvorlageParser_index.html'));

        // This does not actually match the list in the file above, we only return something so the parser doesn't break
        $this->curlBasedDownloader
            ->method('loadUrl')
            ->willReturn(file_get_contents(__DIR__ . '/data/StadtratsvorlageParser_Dokument1.html')); // Dummy return

        $parsed = $this->parser->parseMonth(2021, 1);

        $this->assertCount(77, $parsed);

        $this->assertSame(6950639, $parsed[0]->id);
        $this->assertSame('./sitzungsvorlage/detail/6950639', $parsed[0]->link);
        $this->assertSame('Auswirkungen der Entscheidung des Bundesverwaltungsgerichts (BVerwG) vom 09.11.2021 auf die Vo...', $parsed[0]->titleShortened);
        $this->assertSame('2021-12-14', $parsed[0]->gestelltAm->format('Y-m-d'));

        $this->assertSame(6936565, $parsed[76]->id);
        $this->assertSame('./sitzungsvorlage/detail/6936565', $parsed[76]->link);
        $this->assertSame("Stadtbezirksbudget\nKreisjugendring München-Stadt, Musisches Zentrum\nAnschaffung von Laptops, Ta...", $parsed[76]->titleShortened);
        $this->assertSame('2021-12-01', $parsed[76]->gestelltAm->format('Y-m-d'));
    }

    public function testParseVorlage1()
    {
        $this->curlBasedDownloader
            ->expects($this->exactly(2))
            ->method('loadUrl')
            ->willReturnOnConsecutiveCalls(
                file_get_contents(__DIR__ . '/data/StadtratsvorlageParser_Dokument1.html'),
                file_get_contents(__DIR__ . '/data/StadtratsvorlageParser_Antragsliste1.html')
            );

        $this->stadtratsantragParser
            ->expects($this->exactly(2))
            ->method('parse')
            ->withConsecutive([6447830], [6447494])
            ->willReturn(new Antrag());

        $vorlage = $this->parser->parse(6752652);
        $this->assertSame("Aufbau eines Referats für Klima- und Umweltschutz und eines Gesundheitsreferats\n- IT-Teil (öffentliche Vorlage)", $vorlage->betreff);
        $this->assertSame('20-26/V04180', $vorlage->antrags_nr);
        $this->assertSame('Endgültiger Beschluss', $vorlage->status);
    }
}
