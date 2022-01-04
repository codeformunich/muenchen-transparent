<?php

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StadtratTerminParserTest extends TestCase
{
    /** @var BrowserBasedDowloader|MockObject */
    private $browserBasedDownloader;

    /** @var CurlBasedDownloader|MockObject */
    private $curlBasedDownloader;

    /** @var StadtratsantragParser|MockObject */
    private $stadtratsantragParser;

    private ?StadtratTerminParser $parser = null;

    public function setUp(): void
    {
        $this->browserBasedDownloader = $this->createMock(BrowserBasedDowloader::class);
        $this->curlBasedDownloader = $this->createMock(CurlBasedDownloader::class);
        $this->stadtratsantragParser = $this->createMock(StadtratsantragParser::class); // @TODO Used?
        $this->parser = new StadtratTerminParser($this->browserBasedDownloader, $this->curlBasedDownloader);
        CurlBasedDownloader::setInstance($this->curlBasedDownloader);
    }


    public function testDownloadCalendarEntryWithDependencies_withPublic()
    {

    }
}
