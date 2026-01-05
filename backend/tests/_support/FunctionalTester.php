<?php
namespace backend\tests;

use Codeception\Actor;

/**
 * Inherited Methods
 * @method void amOnPage($page)
 * @method void see($text, $selector = null)
 * @method void click($selector)
 * @method void fillField($field, $value)
 * @method void submitForm($selector, $params)
 * @method void seeElement($selector)
 * @method void dontSee($text)
 */
class FunctionalTester extends Actor
{
    use _generated\FunctionalTesterActions;
}
