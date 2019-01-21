<?php
namespace OxidEsales\EshopCommunity\Tests\Codeception\Step\Acceptance;

use OxidEsales\Codeception\Module\Translation\Translator;

class UserRegistration extends \OxidEsales\EshopCommunity\Tests\Codeception\AcceptanceTester
{
    public function registerUser($userLoginDataToFill, $userDataToFill, $addressDataToFill)
    {
        $I = $this;
        $breadCrumbName = Translator::translate("PAGE_TITLE_REGISTER");
        $registrationPage = new \OxidEsales\Codeception\Page\UserRegistration($I);
        $registrationPage->enterUserLoginData($userLoginDataToFill)
            ->enterUserData($userDataToFill)
            ->enterAddressData($addressDataToFill)
            ->registerUser();

        $registrationPage->seeOnBreadCrumb($breadCrumbName);
        $I->see(Translator::translate('MESSAGE_WELCOME_REGISTERED_USER'));
    }
}