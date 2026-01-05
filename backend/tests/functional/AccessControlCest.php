<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;

class AccessControlCest
{
    public function guestIsRedirectedToLogin(FunctionalTester $I)
    {
        $I->amOnPage('/meter/index');
        $I->seeInCurrentUrl('login');
    }
}
