<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class OparlTester extends \Codeception\Actor
{
    use _generated\OparlTesterActions;
        
    /**
     * Checks if the generic requirements for every OParl-response are met:
     * - the HTTP status code is 200 and the headers are correctly set
     * - the response is valid json and equals the json given as $oparl_object
     * - it is either an external list or an oparl object
     * - all URLs linked to exist
     */
    public function getOParl($url, $skip_extended_checks = false) {
        $this->sendGET($url);
        $this->setVariables($url, $skip_extended_checks);
        $this->seeResponseCodeIs(200);
        $this->seeHttpHeader('Content-Type', 'application/json');
        $this->seeHttpHeader('Access-Control-Allow-Origin', '*');
        $this->seeResponseIsJson();

        if ($skip_extended_checks)
            return;
        
        $this->seeOParlFile();
        
        // Grab the url used in the config and build an url regex based on it
        $config = \Codeception\Configuration::config();
        $apiSettings = \Codeception\Configuration::suiteSettings('oparl', $config);
        $base_url = $apiSettings['modules']['enabled'][1]['REST']['url'];
        $oparl_url_regex = '~"(' . $base_url . '[^"]*)"~';

        // Check that the returned json object is either an external list or an oparl object
        if (array_key_exists('items', $this->getTree())) {
            $this->assertTrue(is_array($this->getTree()->items));
        } else if (array_key_exists('id', $this->getTree()) && array_key_exists('type', $this->getTree())) {
            // check that the id is correct
            $host_url = preg_replace('~^([^/]*//[^/]*).*$~', '$1', $base_url);
            $query_url = rtrim($host_url . $this->grabFromCurrentUrl(), '/');
            $this->assertEquals($query_url, $this->getTree()->id);

            // Check that the typ is a OParl type
            // The url either ends with /[type]/[id] or with /[type]/[subtype]/[id],
            // so we can easily extract the type (in lower case) from the url
            $type = preg_replace('~^.*?/([a-z]+)(/[a-z]+)?/\d+$~', '$1', $this->grabFromCurrentUrl());
            // There's an exception for the system object as it is the entry object
            if ($type == "/oparl/v1.0/")
                $type = "system";
            $this->assertRegExp('~https:\/\/oparl.org\/schema\/1.0\/' . $type . '~i',  $this->getTree()->type);
        } else {
            $this->fail('Returned JSON was neither an object nor an external list');
        }

        // Check that all other oparl objects linked to exist
        preg_match_all($oparl_url_regex, $this->getUglyResponse(), $matches);
        foreach ($matches[1] as $key => $value) {
            codecept_debug('URL validity check for ' . $value);
            $this->sendGET($value);
            $this->seeResponseCodeIs(200);
        }     
    }
}
