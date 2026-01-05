<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;

class MeterAccessCest
{
    public function authenticatedUserCanAccessMeterIndex(FunctionalTester $I)
    {
        // login real
        $I->amOnPage('/site/login');
        $I->fillField('#loginform-username', 'admin');
        $I->fillField('#loginform-password', '12345678');
        $I->click('Sign In');

        $I->amOnPage('/meter/index');
        $I->dontSeeResponseCodeIs(403);
        $I->dontSeeResponseCodeIs(404);
    }
}
