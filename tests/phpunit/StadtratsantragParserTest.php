<?php

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StadtratsantragParserTest extends TestCase
{
    /** @var BrowserBasedDowloader|MockObject */
    private $browserBasedDownloader;

    /** @var CurlBasedDownloader|MockObject */
    private $curlBasedDownloader;

    private ?StadtratsantragParser $parser = null;

    public function setUp(): void
    {
        $this->browserBasedDownloader = $this->createMock(BrowserBasedDowloader::class);
        $this->curlBasedDownloader = $this->createMock(CurlBasedDownloader::class);
        $this->parser = new StadtratsantragParser($this->browserBasedDownloader, $this->curlBasedDownloader);
        CurlBasedDownloader::setInstance($this->curlBasedDownloader);
    }


    public function testParseMonth()
    {
        $this->browserBasedDownloader
            ->method('downloadDocumentTypeListForPeriod')
            ->willReturn(file_get_contents(__DIR__ . '/data/StadtratsantragParser_index.html'));

        // This does not actually match the list in the file above, we only return something so the parser doesn't break
        $this->curlBasedDownloader
            ->method('loadUrl')
            ->willReturn(file_get_contents(__DIR__ . '/data/StadtratsantragParser_Antrag1.html'));

        $parsed = $this->parser->parseMonth(2021, 1);

        $this->assertCount(2, $parsed);

        $this->assertSame(6878706, $parsed[0]->id);
        $this->assertSame('./antrag/detail/6878706', $parsed[0]->link);
        $this->assertSame('Neubau Kulturbürgerhaus Pasing an der Offenbachstraße', $parsed[0]->titleShortened);
        $this->assertSame('2021-10-29', $parsed[0]->gestelltAm->format('Y-m-d'));

        $this->assertSame(123456, $parsed[1]->id);
        $this->assertSame('./antrag/detail/123456', $parsed[1]->link);
        $this->assertSame('Noch ein Antrag', $parsed[1]->titleShortened);
        $this->assertSame('2021-10-01', $parsed[1]->gestelltAm->format('Y-m-d'));
    }

    public function testParseAntrag1()
    {
        $this->curlBasedDownloader
            ->method('loadUrl')
            ->willReturn(file_get_contents(__DIR__ . '/data/StadtratsantragParser_Antrag1.html'));

        $antrag = $this->parser->parse(6842474);
        $this->assertSame('Bericht über die IAA-Mobility 2021', $antrag->betreff);
        $this->assertSame('20-26/A02018', $antrag->antrags_nr);
        $this->assertSame('Erledigt', $antrag->status);
    }
}
