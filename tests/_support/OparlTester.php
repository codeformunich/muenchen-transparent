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
     * - the HTTP status code is 200
     * - the response is valid json and equals the json given as $oparl_object
     * - it is either an external list or an oparl object
     * - all URLs linked to exist
     */
    function seeOparl($oparl_object) {
        // For debugging and developing it's often very usefull to have the
        // possibility to print something without having to use the debug flag
        $output = new \Codeception\Lib\Console\Output([]);
        //$output->writeln('comment');

        // TODO: CORS
        $this->seeResponseCodeIs(200);
        $this->seeResponseIsJson();
        $this->seeResponseEquals(json_encode(json_decode($oparl_object)));

        // Grab the url used in the config and build an url regex based on it
        $config = \Codeception\Configuration::config();
        $apiSettings = \Codeception\Configuration::suiteSettings('oparl', $config);
        $base_url = $apiSettings['modules']['enabled'][1]['REST']['url'];
        $oparl_url_regex = '~"(' . $base_url . '[^"]*)"~';

        // Check that the returned json object is either an external list or an oparl object
        $tree = json_decode($this->getResponseContent());
        if (array_key_exists('items', $tree)) {
            $this->assertTrue(is_array($tree->items));
        } else if (array_key_exists('id', $tree) && array_key_exists('type', $tree)) {
            // check that the id is correct
            $host_url = preg_replace('~^([^/]*//[^/]*).*$~', '$1', $base_url);
            $query_url = rtrim($host_url . $this->grabFromCurrentUrl(), '/');
            $this->assertEquals($query_url, $tree->id);

            // Check that the typ is a OParl type
            // The url either ends with /[type]/[id] or with /[type]_[subtype]/[id],
            // so we can easily extract the lower case type from the url
            $type = preg_replace('~^.*/([a-z]+)(_[a-z]+)?/\d+$~', '$1', $this->grabFromCurrentUrl());
            // There's an exception for the system object as it is the entry object
            if ($type == "/oparl/v1.0/")
                $type = "system";
            $this->assertRegExp('~https:\/\/oparl.org\/schema\/1.0\/' . $type . '~i',  $tree->type);
        } else {
            $this->fail('Returned JSON was neither an obejct nor an external list');
        }

        // Check that all other oparl objects linked to exist
        preg_match_all($oparl_url_regex, $this->getResponseContent(), $matches);
        foreach ($matches[1] as $key => $value) {
            codecept_debug('URL validity check for ' . $value);
            $this->sendGET($value);
            $this->seeResponseCodeIs(200);
        }
     }
}
