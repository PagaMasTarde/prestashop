<?php

namespace Test\Advanced;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs16Selenium;

/**
 * @requires prestashop16install
 * @requires prestashop16register
 *
 * @group prestashop16advanced
 */
class PaylaterPs16InstallTest extends AbstractPs16Selenium
{
    /**
     * @REQ5 BackOffice should have 2 inputs for setting the public and private API key
     * @REQ6 BackOffice inputs for API keys should be mandatory upon save of the form.
     *
     * @throws  \Exception
     */
    public function testPublicAndPrivateKeysInputs()
    {
        $this->loginToBackOffice();
        $this->getPaylaterBackOffice();

        //2 elements exist:
        $validatorSearch = WebDriverBy::id('pmt_public_key');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $validatorSearch = WebDriverBy::id('pmt_private_key');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);

        //save with empty public Key
        $this->findById('pmt_public_key')->clear();
        $this->findById('module_form_submit_btn')->click();
        $validatorSearch = WebDriverBy::className('module_error');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->assertContains('Please add a Paga+Tarde API Public Key', $this->webDriver->getPageSource());
        $this->findById('pmt_public_key')->clear()->sendKeys($this->configuration['publicKey']);

        //save with empty private Key
        $this->findById('pmt_private_key')->clear();
        $this->findById('module_form_submit_btn')->click();
        $validatorSearch = WebDriverBy::className('module_error');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($validatorSearch);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->assertContains('Please add a Paga+Tarde API Private Key', $this->webDriver->getPageSource());
        $this->findById('pmt_private_key')->clear()->sendKeys($this->configuration['secretKey']);

        $this->quit();
    }

    /**
     * @REQ8 In the backOffice the merchant has to be able to choose between Iframe and Redirect.
     * (both cases should be tested in buy experience test).
     * Item title: "Visualización". Values: Redirect & Iframe. By default: Redirect
     *
     * @throws \Exception
     */
    public function testBuyWithRedirect()
    {
        $this->loginToBackOffice();
        $this->getPaylaterBackOffice();
        $this->findById('redirection')->click();
        $this->findById('module_form_submit_btn')->click();
        $confirmationSearch = WebDriverBy::className('module_confirmation');
        $condition = WebDriverExpectedCondition::textToBePresentInElement(
            $confirmationSearch,
            'All changes have been saved'
        );
        $this->webDriver->wait($condition);
        $this->assertTrue((bool) $condition);

        $this->loginToFrontend();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout(true);
        $paylaterCheckout = WebDriverBy::className('paylater-checkout');
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($paylaterCheckout);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $this->webDriver->findElement($paylaterCheckout)->click();
        $this->verifyUTF8();

        $this->webDriver->get(self::PS16URL.self::BACKOFFICE_FOLDER);
        $this->getPaylaterBackOffice();
        $this->findById('frame')->click();
        $this->findById('module_form_submit_btn')->click();
        $confirmationSearch = WebDriverBy::className('module_confirmation');
        $condition = WebDriverExpectedCondition::textToBePresentInElement(
            $confirmationSearch,
            'All changes have been saved'
        );
        $this->webDriver->wait($condition);
        $this->assertTrue((bool) $condition);
        $this->quit();
    }

    /**
     * @REQ9 BackOffice Simulator Product Page
     * @REQ11 BackOffice Simulator Start and Max installments
     * @REQ12 BackOffice MinAmount (product simulator part)
     * @REQ19 Simulator Shown
     * @REQ20 Simulator Installments check
     * @REQ21 Simulator Min Amount
     *
     * @throws \Exception
     */
    public function testSimulatorInProductPage()
    {
        //TODO REMOVE THIS WHEN ORDERS HAVE SIMULATOR
        return true;

        $this->goToProduct();
        $simulatorDiv = $this->findByClass('PmtSimulator');
        $simulatorType = $simulatorDiv->getAttribute('data-pmt-type');
        $numQuota = $simulatorDiv->getAttribute('data-pmt-num-quota');
        $maxInstallments = $simulatorDiv->getAttribute('data-pmt-max-ins');

        $this->assertEquals(6, $simulatorType);
        $this->assertEquals(3, $numQuota);
        $this->assertEquals(12, $maxInstallments);

        //Check min amount simulator
        $this->loginToBackOffice();
        $this->getPaylaterBackOffice();
        $this->findById('pmt_display_min_amount')->clear()->sendKeys(500);
        $this->findById('module_form_submit_btn')->click();

        $this->goToProduct(false);
        $html = $this->webDriver->getPageSource();
        $this->assertNotContains('PmtSimulator', $html);

        //Change Type, num Quota and Max Installments, Restore min amount
        $this->getPaylaterBackOffice();
        $this->findById('product-simulator-complete')->click();
        $this->findById('pmt_sim_quotes_start')->clear()->sendKeys(5);
        $this->findById('pmt_sim_quotes_max')->clear()->sendKeys(10);
        $this->findById('pmt_display_min_amount')->clear()->sendKeys(1);
        $this->findById('module_form_submit_btn')->click();

        $this->goToProduct();
        $simulatorDiv = $this->findByClass('PmtSimulator');
        $simulatorType = $simulatorDiv->getAttribute('data-pmt-type');
        $numQuota = $simulatorDiv->getAttribute('data-pmt-num-quota');
        $maxInstallments = $simulatorDiv->getAttribute('data-pmt-max-ins');

        $this->assertEquals(2, $simulatorType);
        $this->assertEquals(5, $numQuota);
        $this->assertEquals(10, $maxInstallments);

        //Hide simulator
        $this->getPaylaterBackOffice();
        $this->findById('product-simulator-hide')->click();
        $this->findById('pmt_sim_quotes_start')->clear()->sendKeys(3);
        $this->findById('pmt_sim_quotes_max')->clear()->sendKeys(12);
        $this->findById('module_form_submit_btn')->click();

        $this->goToProduct(false);
        $html = $this->webDriver->getPageSource();
        $this->assertNotContains('PmtSimulator', $html);

        //Restore default simulator
        $this->getPaylaterBackOffice();
        $this->findById('product-simulator-mini')->click();
        $this->findById('pmt_sim_quotes_start')->clear()->sendKeys(3);
        $this->findById('pmt_sim_quotes_max')->clear()->sendKeys(12);
        $this->findById('module_form_submit_btn')->click();

        $this->goToProduct();
        $simulatorDiv = $this->findByClass('PmtSimulator');
        $simulatorType = $simulatorDiv->getAttribute('data-pmt-type');
        $numQuota = $simulatorDiv->getAttribute('data-pmt-num-quota');
        $maxInstallments = $simulatorDiv->getAttribute('data-pmt-max-ins');

        $this->assertEquals(6, $simulatorType);
        $this->assertEquals(3, $numQuota);
        $this->assertEquals(12, $maxInstallments);
        $this->quit();
    }

    /**
     * @REQ14 BackOffice Checkout Title
     * @REQ20 Checkout Title
     *
     * @throws \Exception
     */
    public function testTitleIsEditable()
    {
        $title = 'Instant Financing';
        $newTitle = 'Financiación Instánea';

        $this->loginToFrontend();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout(true);

        $html = $this->webDriver->getPageSource();
        $this->assertContains($title, $html);

        //Change Title
        $this->loginToBackOffice();
        $this->getPaylaterBackOffice();
        $this->findById('pmt_title')->clear()->sendKeys($newTitle);
        $this->findById('module_form_submit_btn')->click();


        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout(true);

        $html = $this->webDriver->getPageSource();
        $this->assertContains($newTitle, $html);

        //Restore Title
        $this->getPaylaterBackOffice();
        $this->findById('pmt_title')->clear()->sendKeys($title);
        $this->findById('module_form_submit_btn')->click();

        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout(true);

        $html = $this->webDriver->getPageSource();
        $this->assertContains($title, $html);
        $this->quit();
    }

    /**
     * @REQ17 BackOffice Panel should have visible Logo and links
     *
     * @throws \Exception
     */
    public function testBackOfficeHasLogoAndLinkToPmt()
    {
        //Change Title
        $this->loginToBackOffice();
        $this->getPaylaterBackOffice();
        $html = $this->webDriver->getPageSource();
        $this->assertContains('logo_pagamastarde.png', $html);
        $this->assertContains('Login Paga+Tarde', $html);
        $this->assertContains('https://bo.pagamastarde.com', $html);
        $this->quit();
    }

    /**
     * @REQ10 BackOffice Simulator Checkout Page
     * @REQ27 Payment method Logo
     * @REQ28 Simulator Shown
     * @REQ29 Simulator Installments check
     * @REQ30 Simulator Min Amount
     *
     * @throws \Exception
     */
    public function testSimulatorInCheckoutPage()
    {
        //TODO REMOVE THIS WHEN ORDERS HAVE SIMULATOR
        return true;

        $this->loginToFrontend();
        $this->goToProduct();
        $pk = $this->webDriver->executeScript('return pmtClient.simulator.getPublicKey()');
        $this->assertEquals($this->configuration['publicKey'], $pk);
        $this->addProduct();
        $this->goToCheckout(true, false);
        $pk = $this->webDriver->executeScript('return pmtClient.simulator.getPublicKey()');
        $this->assertEquals($this->configuration['publicKey'], $pk);
        $simulatorDiv = $this->findByClass('PmtSimulator');
        $simulatorType = $simulatorDiv->getAttribute('data-pmt-type');
        $numQuota = $simulatorDiv->getAttribute('data-pmt-num-quota');
        $maxInstallments = $simulatorDiv->getAttribute('data-pmt-max-ins');

        $this->assertEquals(6, $simulatorType);
        $this->assertEquals(3, $numQuota);
        $this->assertEquals(12, $maxInstallments);

        //Payment Method Logo:
        $html = $this->webDriver->getPageSource();
        $this->assertContains('logo-64x64.png', $html);

        //Check min amount simulator
        $this->loginToBackOffice();
        $this->getPaylaterBackOffice();
        $this->findById('pmt_display_min_amount')->clear()->sendKeys(500);
        $this->findById('module_form_submit_btn')->click();

        $this->goToProduct(false);
        $this->addProduct();
        $this->goToCheckout(true, false);
        $html = $this->webDriver->getPageSource();
        $this->assertNotContains('PmtSimulator', $html);

        //Change Type, num Quota and Max Installments, Restore min amount
        $this->getPaylaterBackOffice();
        $this->findById('checkout-simulator-complete')->click();
        $this->findById('pmt_sim_quotes_start')->clear()->sendKeys(5);
        $this->findById('pmt_sim_quotes_max')->clear()->sendKeys(10);
        $this->findById('pmt_display_min_amount')->clear()->sendKeys(1);
        $this->findById('module_form_submit_btn')->click();

        $this->goToProduct(false);
        $this->addProduct();
        $this->goToCheckout(true, false);
        $simulatorDiv = $this->findByClass('PmtSimulator');
        $simulatorType = $simulatorDiv->getAttribute('data-pmt-type');
        $numQuota = $simulatorDiv->getAttribute('data-pmt-num-quota');
        $maxInstallments = $simulatorDiv->getAttribute('data-pmt-max-ins');

        $this->assertEquals(2, $simulatorType);
        $this->assertEquals(5, $numQuota);
        $this->assertEquals(10, $maxInstallments);

        //Hide simulator
        $this->getPaylaterBackOffice();
        $this->findById('checkout-simulator-hide')->click();
        $this->findById('pmt_sim_quotes_start')->clear()->sendKeys(3);
        $this->findById('pmt_sim_quotes_max')->clear()->sendKeys(12);
        $this->findById('module_form_submit_btn')->click();

        $this->goToProduct(false);
        $this->addProduct();
        $this->goToCheckout(true, false);
        $html = $this->webDriver->getPageSource();
        $this->assertNotContains('PmtSimulator', $html);

        //Restore default simulator
        $this->getPaylaterBackOffice();
        $this->findById('checkout-simulator-mini')->click();
        $this->findById('pmt_sim_quotes_start')->clear()->sendKeys(3);
        $this->findById('pmt_sim_quotes_max')->clear()->sendKeys(12);
        $this->findById('module_form_submit_btn')->click();

        $this->goToProduct(false);
        $this->addProduct();
        $this->goToCheckout(true, false);
        $simulatorDiv = $this->findByClass('PmtSimulator');
        $simulatorType = $simulatorDiv->getAttribute('data-pmt-type');
        $numQuota = $simulatorDiv->getAttribute('data-pmt-num-quota');
        $maxInstallments = $simulatorDiv->getAttribute('data-pmt-max-ins');

        $this->assertEquals(6, $simulatorType);
        $this->assertEquals(3, $numQuota);
        $this->assertEquals(12, $maxInstallments);
        $this->quit();
    }

    /**
     * @REQ35 Amount matches checkout page
     * @REQ36 Back to shop
     *
     * @throws \Exception
     */
    public function testAmountAndKoUrlInPmtForm()
    {
        //Get KO Url:
        $this->loginToBackOffice();
        $this->getPaylaterBackOffice();
        $koUrl = $this->findById('pmt_url_ko')->getAttribute('value');

        //Verify Amount:
        $this->loginToFrontend();
        $this->goToProduct();
        $this->addProduct();
        $this->goToCheckout(true, false);
        $totalPrice = str_replace(' ', '', $this->findById('total_price')->getText());
        $this->verifyPaylater();
        $html = $this->webDriver->getPageSource();
        $this->assertContains($totalPrice, $html);
        $this->assertNotContains('Women', $html);
        $backToStoreButton = WebDriverBy::name('back_to_store_button');
        $condition = WebDriverExpectedCondition::elementToBeClickable($backToStoreButton);
        $this->waitUntil($condition);
        $this->assertTrue((bool) $condition);
        $backToStoreButton = $this->findByName('back_to_store_button');
        $this->assertEquals($backToStoreButton->getAttribute('href'), $koUrl);
        $this->webDriver->executeScript('document.getElementsByName("back_to_store_button")[0].click();');
        $this->assertEquals($koUrl, $this->webDriver->getCurrentURL());
        $this->quit();
    }
}
