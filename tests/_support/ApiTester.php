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
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

   /**
    * Checks if the generic requirements for every OParl-response are met
    */
    function seeOparl($oparl_object) {
        $this->seeResponseCodeIs(200);
        // TODO: CORS
        $this->seeResponseIsJson();
        $this->seeResponseEquals(json_encode(json_decode($oparl_object)));
    }
}
