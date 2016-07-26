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

        $startsWith = function ($haystack, $needle) {
            return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
        };

        // Grab the url used in the config and build an url regex based on it
        $config = \Codeception\Configuration::config();
        $apiSettings = \Codeception\Configuration::suiteSettings('oparl', $config);

        $base_url = $apiSettings['modules']['enabled'][1]['REST']['url'];
        $host_url = preg_replace('~^([^/]*//[^/]*).*$~', '$1', $base_url);
        $query_url = rtrim($host_url . $this->grabFromCurrentUrl(), '/');

        $oparl_url_regex = '~"(' . $base_url . '[^"]*)"~';

        // Check that the returned json object is either an external list or an oparl object
        $tree = $this->getResponseAsTree();
        if (array_key_exists('data', $tree)) {
            // See "2.5.4 Paginierung" for details
            $this->assertTrue(is_array($tree->data), "data");
            $this->assertTrue(is_object($tree->pagination), "pagination");
            foreach ($tree->pagination as $key => $value) {
                $this->assertTrue(is_int($value), "is int");
            }
            $this->assertTrue(is_object($tree->links), "links");
            //$this->assertTrue(isset($tree->links->self)); // TODO
            //$this->assertEquals($tree->links->self, $query_url);
            foreach ($tree->links as $key => $value) {
                $this->assertTrue(is_string($value), "is tring");
                $this->assertTrue($startsWith($value, $base_url), "starts with base url");
            }
        } else if (array_key_exists('id', $tree)) {
            // See "3.3 Eigenschaften mit Verwendung in mehreren Objekttypen" for details
            $this->assertTrue(isset($tree->type));
            //$this->assertTrue(isset($tree->created));
            //$this->assertTrue(isset($tree->modified));
            $this->assertEquals($tree->id, $query_url);

            // Check that the typ is a OParl type
            // The url either ends with /[type]/[id] or with /[type]/[subtype]/[id],
            // so we can easily extract the type (in lower case) from the url
            $type = preg_replace('~^.*?/([a-z]+)(/[a-z]+)?/\d+$~', '$1', $this->grabFromCurrentUrl());
            // There's an exception for the system object as it is the entry object
            if ($type == "/oparl/v1.0/")
                $type = "system";
            $this->assertRegExp('~https:\/\/schema.oparl.org\/1.0\/' . $type . '~i',  $tree->type);
        } else {
            $this->fail('Returned JSON was neither an object nor an external list');
        }

        // Check that all other oparl objects linked to exist
        preg_match_all($oparl_url_regex, $this->getCompressedResponse(), $matches);
        foreach ($matches[1] as $url) {
            if ($this->isURLKnown($url))
                continue;
            codecept_debug('URL validity check for ' . $url);
            $this->sendGET($url);
            $this->seeResponseCodeIs(200);
        }
    }
}
