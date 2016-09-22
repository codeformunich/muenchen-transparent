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
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

   /**
    * Define custom actions here
    */
    function seeResponseIsHtml($page) {
        $this->amOnPage($page);
        codecept_debug($this->getScenario()->current('env'));
        // FIXME: Workaround for travis ci because it doesn't support java 8
        if (strpos($this->getScenario()->current('env'), 'nohtmlvalidation') === false) {
            $this->validateHTML();
        }
        //$this->validatePa11y('WCAG2AA', ["tag_add_form"]); // TODO: Wieder aktivieren und alle Fehler abarbeiten
    }
}
