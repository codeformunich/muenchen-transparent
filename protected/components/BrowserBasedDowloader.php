<?php

declare(strict_types=1);

use HeadlessChromium\Browser\ProcessAwareBrowser;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;

class BrowserBasedDowloader
{
    private ProcessAwareBrowser $browser;
    public Page $page;

    public function __construct()
    {
        $browserFactory = new BrowserFactory();
        $this->browser = $browserFactory->createBrowser([
            'enableImages' => true, // necessary for JavaScript to work
            //'debugLogger' => 'php://stdout',
            //'headless' => false,
            'userAgent' => 'München Transparent',
        ]);
        $this->page = $this->browser->createPage();
    }

    public function close()
    {
        $this->browser->close();
    }

    public function waitForElementToAppear(string $query): void
    {
        $this->page->evaluate('new Promise(resolve => {
            let interval = setInterval(() => {
                if (document.querySelector("' . $query . '")) {
                    clearInterval(interval);
                    resolve();
                }
            }, 100);
        })')->waitForResponse(5000);
    }

    public function clickJs(string $selector): void
    {
        $this->page->evaluate('document.querySelector("' . $selector . '").dispatchEvent(new MouseEvent("click"))')->waitForResponse();
    }

    public function getInnerHtml(string $selector): string
    {
        return $this->page->evaluate('document.querySelector("'.$selector.'").innerHTML')->getReturnValue();
    }

    public function seeElement(string $selector): bool
    {
        return $this->page->evaluate('!!document.querySelector("'.$selector.'")')->getReturnValue();
    }
}
