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
    public const DOCUMENT_BV_ANFRAGEN = '6';
    public const DOCUMENT_SITZUNG = '7';
    public const DOCUMENT_BESCHLUSS = '8';
    public const DOCUMENT_GREMIUM = '9';
    public const DOCUMENT_FRAKTION_GRUPPE = '10';
    public const DOCUMENT_PERSON = '11';

    public const PERSON_TYPE_STADTRAT = 'strmitglieder';
    public const PERSON_TYPE_REFERENTINNEN = 'referenten';
    public const PERSON_TYPE_BA_MITGLIEDER = 'bamitglieder';
    public const PERSON_TYPE_BA_BEAUFTRAGTE = 'babeauftragter';

    public const MEMBERSHIP_TYPE_STR_AUSSCHUESSE = 'strausschuesse';

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
        $this->page->setUserAgent(RISTools::STD_USER_AGENT);
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

    private function readPaginatedContent(string $listClass): string
    {
        $html = '';
        $goon = true;
        for ($i = 0; $i < 100 && $goon; $i++) {
            $page = intval($this->getInnerHtml($listClass . ' .btn-pagelink.btn-selected span'));

            if ($page !== $i + 1) {
                throw new ParsingException('Switched to page ' . ($i + 1) . ', but HTML indicates ' . $page);
            }

            $html .= $this->getInnerHtml($listClass . ' .list-group-flush');

            $nextChildNodes = 'document.querySelector(".colors_suche button.btn-selected").parentElement.nextElementSibling.childNodes.length';
            if ($this->page->evaluate($nextChildNodes)->getReturnValue() > 0) {
                $this->page->evaluate('document.querySelector("' . $listClass . ' .list-group-flush").remove()')->waitForResponse();
                $this->page->evaluate('$("' . $listClass . ' button.btn-selected").first().parent().next().find("button").click()')->waitForResponse();
                $this->waitForElementToAppear($listClass . ' .list-group-flush');
            } else {
                $goon = false;
            }
        }

        return $html;
    }
    
    public function downloadDocumentTypeListForPeriod(string $type, \DateTime $from, \DateTime $to): string
    {
        $html = '';

        $this->open();

        try {
            $this->page->navigate(RIS_URL_PREFIX . 'erweitertesuche')->waitForNavigation();
            sleep(1);
            $this->page->evaluate('$("#id3").val("' . $type . '").trigger("change");');
            $this->waitForElementToAppear('.colors_suche form input[type=date][name=von]');

            $this->page->evaluate('document.querySelector(".colors_suche form input[type=date][name=von]").value = "' . $from->format('Y-m-d') . '"');
            $this->page->evaluate('document.querySelector(".colors_suche form input[type=date][name=bis]").value = "' . $to->format('Y-m-d') . '"');
            $this->clickJs('.colors_suche form button[type=submit]');
            $this->page->waitForReload();

            $innerText = $this->page->evaluate('document.querySelector(".colors_suche").innerText')->getReturnValue();
            if (str_contains($innerText, 'Es wurden keine Einträge gefunden!')) {
                return '';
            }

            $html = $this->readPaginatedContent('.colors_suche');
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
            $html = $this->readPaginatedContent('.colors_person');
        } finally {
            $this->close();
        }

        return $html;
    }

    public function downloadPersonsMembershipList(int $personId, string $membershipType): string
    {
        $html = '';

        $this->open();

        try {
            $this->page->navigate(RIS_URL_PREFIX . 'person/detail/' . $personId . '/?tab=' . $membershipType)->waitForNavigation();
            $html = $this->readPaginatedContent('.colors_person');
        } finally {
            $this->close();
        }

        return $html;
    }
}
