<?php

declare(strict_types=1);

use HeadlessChromium\Browser\ProcessAwareBrowser;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;

class BrowserBasedDowloader
{
    public const DOCUMENT_STADTRAT_ANTRAG = '0';
    public const DOCUMENT_SITZUNGSVORLAGEN = '1';
    public const DOCUMENT_BA_ANTRAG = '2';
    public const DOCUMENT_BA_TOP = '3';
    public const DOCUMENT_BA_INITIATIVE = '4';
    public const DOCUMENT_BV_EMPFEHLUNG = '5';
    public const DOCUMENT_SITZUNG = '6';
    public const DOCUMENT_BESCHLUSS = '7';
    public const DOCUMENT_GREMIUM = '8';
    public const DOCUMENT_FRAKTION_GRUPPE = '9';
    public const DOCUMENT_PERSON = '10';

    public const PERSON_TYPE_STADTRAT = 'strmitglieder';
    public const PERSON_TYPE_REFERENTINNEN = 'referenten';
    public const PERSON_TYPE_BA_MITGLIEDER = 'strmitglieder';
    public const PERSON_TYPE_BA_BEAUFTRAGTE = 'babeauftragter';

    private string $browserPath;
    private ?ProcessAwareBrowser $browser = null;
    private ?Page $page = null;

    public function __construct()
    {
        $this->browserPath = PATH_CHROME_BROWSER;
    }

    private function open(): void
    {
        if ($this->browser) {
            return;
        }
        $browserFactory = new BrowserFactory($this->browserPath);
        $this->browser = $browserFactory->createBrowser([
            'enableImages' => true, // necessary for JavaScript to work
            //'debugLogger' => 'php://stdout',
            //'headless' => false,
            'userAgent' => 'München Transparent',
        ]);
        $this->page = $this->browser->createPage();
    }

    public function close(): void
    {
        $this->browser->close();
        $this->page = null;
        $this->browser = null;
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
        $this->page->evaluate('$("' . $selector . '").first().click()')->waitForResponse();
    }

    public function getInnerHtml(string $selector): string
    {
        return $this->page->evaluate('document.querySelector("'.$selector.'").innerHTML')->getReturnValue();
    }

    public function seeElement(string $selector): bool
    {
        return $this->page->evaluate('!!document.querySelector("'.$selector.'")')->getReturnValue();
    }
    
    public function downloadDocumentTypeListForPeriod(string $type, \DateTime $from, \DateTime $to): string
    {
        $html = '';

        $this->open();

        try {
            $this->page->navigate(RIS_URL_PREFIX . 'erweitertesuche')->waitForNavigation();

            $this->page->evaluate('$("#id3").val("' . $type . '").trigger("change");');
            $this->waitForElementToAppear('.colors_suche form input[type=date][name=von]');

            $this->page->evaluate('document.querySelector(".colors_suche form input[type=date][name=von]").value = "' . $from->format('Y-m-d') . '"');
            $this->page->evaluate('document.querySelector(".colors_suche form input[type=date][name=bis]").value = "' . $to->format('Y-m-d') . '"');
            $this->clickJs('.colors_suche form button[type=submit]');
            $this->page->waitForReload();

            $goon = true;
            for ($i = 0; $i < 100 && $goon; $i++) {
                $page = intval($this->getInnerHtml('.colors_suche .btn-pagelink[disabled] span'));

                if ($page !== $i + 1) {
                    throw new ParsingException('Switched to page ' . ($i + 1) . ', but HTML indicates ' . $page);
                }

                $html .= $this->getInnerHtml('.colors_suche .list-group-flush');
                if ($this->seeElement('.colors_suche a[rel=next]')) {
                    $this->page->evaluate('document.querySelector(".colors_suche .list-group-flush").remove()')->waitForResponse();
                    $this->clickJs('.colors_suche a[rel=next]');
                    $this->waitForElementToAppear('.colors_suche .list-group-flush');
                } else {
                    $goon = false;
                }
            }
        } finally {
            $this->close();
        }

        return $html;
    }

    public function downloadPersonList(string $type): string
    {
        $html = '';

        $this->open();

        try {
            $this->page->navigate(RIS_URL_PREFIX . 'person/' . $type)->waitForNavigation();

            $goon = true;
            for ($i = 0; $i < 100 && $goon; $i++) {
                $page = intval($this->getInnerHtml('.colors_person .btn-pagelink[disabled] span'));

                if ($page !== $i + 1) {
                    throw new ParsingException('Switched to page ' . ($i + 1) . ', but HTML indicates ' . $page);
                }

                $html .= $this->getInnerHtml('.colors_person .list-group-flush');
                if ($this->seeElement('.colors_person a[rel=next]')) {
                    $this->page->evaluate('document.querySelector(".colors_person .list-group-flush").remove()')->waitForResponse();
                    $this->clickJs('.colors_person a[rel=next]');
                    $this->waitForElementToAppear('.colors_person .list-group-flush');
                } else {
                    $goon = false;
                }
            }
        } finally {
            $this->close();
        }

        return $html;
    }
}
