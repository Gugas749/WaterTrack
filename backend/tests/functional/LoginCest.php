<?php

use backend\tests\FunctionalTester;

use common\fixtures\UserFixture;

/**
 * Class LoginCest
 */
class LoginCest
{
    /**
     * Load fixtures before db transaction begin
     * Called in _before()
     * @see \Codeception\Module\Yii2::_before()
     * @see \Codeception\Module\Yii2::loadFixtures()
     * @return array
     */
    public function _fixtures()
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'login_data.php'
            ]
        ];
    }
    
    /**
     * @param FunctionalTester $I
     */
    public function loginUser(FunctionalTester $I)
    {
        $I->amOnPage('/site/login');

        $I->fillField('#loginform-username', 'admin');
        $I->fillField('#loginform-password', '12345678');
        $I->click('Sign In');

        $I->seeInCurrentUrl('/dashboard');
        $I->see('admin');
    }

    public function loginComSucesso(FunctionalTester $I)
    {
        $I->amOnPage('/site/login');

        $I->see('Sign in');
        $I->fillField('#loginform-username', 'admin');
        $I->fillField('#loginform-password', '12345678');
        $I->click('Sign In');

        $I->seeInCurrentUrl('/dashboard');
        $I->see('admin');
    }

}
