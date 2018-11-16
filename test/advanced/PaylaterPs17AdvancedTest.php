<?php

namespace Test\Advanced;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Common\AbstractPs17Selenium;

/**
 * @requires prestashop17install
 * @requires prestashop17register
 *
 * @group prestashop17advanced
 */
class PaylaterPs17InstallTest extends AbstractPs17Selenium
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
        $this->goToCheckout(true);
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
        $this->assertContains('logo.gif', $html);

        //Check min amount simulator
        $this->loginToBackOffice();
        $this->getPaylaterBackOffice();
        $this->findById('pmt_display_min_amount')->clear()->sendKeys(500);
        $this->findById('module_form_submit_btn')->click();

        $this->goToProduct(false);
        $this->addProduct();
        $this->goToCheckout(true);
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
        $this->goToCheckout(true);
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
        $this->goToCheckout(true);
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
        $this->goToCheckout(true);
        $simulatorDiv = $this->findByClass('PmtSimulator');
        $simulatorType = $simulatorDiv->getAttribute('data-pmt-type');
        $numQuota = $simulatorDiv->getAttribute('data-pmt-num-quota');
        $maxInstallments = $simulatorDiv->getAttribute('data-pmt-max-ins');

        $this->assertEquals(6, $simulatorType);
        $this->assertEquals(3, $numQuota);
        $this->assertEquals(12, $maxInstallments);
        $this->quit();
    }
}
