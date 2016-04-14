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
     * Checks if the generic requirements for every OParl-response are met
     */
    function seeOparl($oparl_object) {
        $this->seeResponseCodeIs(200);
        // TODO: CORS
        $this->seeResponseIsJson();
        $this->seeResponseEquals(json_encode(json_decode($oparl_object)));

        // grab the url used in the config and build an url regex based on it
        $config = \Codeception\Configuration::config();
        $apiSettings = \Codeception\Configuration::suiteSettings('oparl', $config);
        $base_url = $apiSettings['modules']['enabled'][1]['REST']['url'];
        $oparl_url = '/"(' . preg_quote($base_url, '/') . '[^\"]*)"/';

        preg_match_all($oparl_url, $this->getResponseContent(), $matches);
        foreach ($matches[1] as $key => $value) {
            codecept_debug("URL validity check for " . $value);
            $this->sendGET($value);
            $this->seeResponseCodeIs(200);
        }
     }
}
