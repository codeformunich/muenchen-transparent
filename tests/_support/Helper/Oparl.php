<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Oparl extends \Codeception\Module
{
    /*
     * expose PhpBrowser's "_getResponseContent()" and remove the escape sequences
     */
    public function getResponseContent() {
        return $this->getModule('REST')->response;
    }
}
