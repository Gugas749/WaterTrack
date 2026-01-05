<?php

namespace backend\tests\functional;

use backend\tests\FunctionalTester;

class MeterReadingGuestCest
{
    public function guestCannotAccessMeterSection(FunctionalTester $I)
    {
        $I->amOnPage('/meter/index');
        $I->seeInCurrentUrl('login');
    }
}
