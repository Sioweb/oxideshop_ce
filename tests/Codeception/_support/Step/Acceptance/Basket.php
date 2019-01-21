<?php
namespace OxidEsales\EshopCommunity\Tests\Codeception\Step\Acceptance;

use OxidEsales\Codeception\Page\UserCheckout;
use OxidEsales\Codeception\Page\Basket as BasketPage;
use OxidEsales\Codeception\Module\Translation\Translator;

class Basket extends \OxidEsales\EshopCommunity\Tests\Codeception\AcceptanceTester
{
    /**
     * @param $productId
     * @param $amount
     */
    public function addProductToBasket($productId, $amount)
    {
        $I = $this;
        //add Product to basket
        // $params['cl'] = $controller;
        $params['fnc'] = 'tobasket';
        $params['aid'] = $productId;
        $params['am'] = $amount;
        $params['anid'] = $productId;
        $I->amOnPage('/index.php?'.http_build_query($params));
    }
    /**
     * @param $productId
     * @param $amount
     * @param $controller
     *
     * @return mixed
     */
    public function addProductToBasketAndOpen($productId, $amount, $controller)
    {
        $I = $this;
        //add Product to basket
        $params['cl'] = $controller;
        $params['fnc'] = 'tobasket';
        $params['aid'] = $productId;
        $params['am'] = $amount;
        $params['anid'] = $productId;
        $I->amOnPage(BasketPage::route($params));
        if ($controller === 'user') {
            $userCheckoutPage = new UserCheckout($I);
            $breadCrumbName = Translator::translate("ADDRESS");
            $userCheckoutPage->seeOnBreadCrumb($breadCrumbName);
            return $userCheckoutPage;
        } else {
            $basketPage = new BasketPage($I);
            $breadCrumbName = Translator::translate("CART");
            $basketPage->seeOnBreadCrumb($breadCrumbName);
            return $basketPage;
        }
    }
}