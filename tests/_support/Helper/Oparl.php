<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Oparl extends \Codeception\Module
{
    /*
     * returns the unescaped response content
     */
    public function getResponseContent() {
        return stripslashes($this->getModule('REST')->response);
    }
}
