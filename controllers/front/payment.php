<?php
/**
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 */
use Afterpay\SDK\HTTP\Request\CreateCheckout;
use Afterpay\SDK\MerchantAccount as ClearpayMerchantAccount;

require_once('AbstractController.php');

/**
 * Class ClearpayRedirectModuleFrontController
 */
class ClearpayPaymentModuleFrontController extends AbstractController
{
    /** @var string $language */
    protected $language;

    /**
     * Process Post Request
     *
     * @throws \Exception
     */
    public function postProcess()
    {
        $context = Context::getContext();
        $currency = $context->currency->iso_code;

        /** @var Cart $cart */
        $cart = $context->cart;
        $shippingAddress = new Address($cart->id_address_delivery);
        $shippingCountryObj = new Country($shippingAddress->id_country);
        $shippingCountryCode = $shippingCountryObj->iso_code;
        $shippingStateObj = new State($shippingAddress->id_state);
        $shippingStateCode = '';
        if (!empty($shippingAddress->id_state) && !empty($state_object)) {
            $shippingStateCode = $shippingStateObj->iso_code;
        }

        $billingAddress = new Address($cart->id_address_invoice);
        $billingCountryCode = Country::getIsoById($billingAddress->id_country);
        $billingStateObj = new State($billingAddress->id_state);
        $billingStateCode = '';
        if (!empty($billingAddress->id_state) && !empty($state_object)) {
            $billingStateCode = $billingStateObj->iso_code;
        }

        $discountAmount = $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);

        /** @var Carrier $carrier */
        $carrier = new Carrier($cart->id_carrier);

        /** @var Customer $customer */
        $customer = $context->customer;

        if (!$cart->id) {
            Tools::redirect('index.php?controller=order');
        }

        $urlToken = Tools::strtoupper(md5(uniqid(rand(), true)));

        $query = array(
            'id_cart' => $cart->id,
            'key' => $cart->secure_key,
        );

        $koUrl = $context->link->getPageLink(
            'order',
            null,
            null,
            array('step'=>3)
        );
        $cancelUrl = (Clearpay::getExtraConfig('URL_KO') !== '') ? Clearpay::getExtraConfig('URL_KO', null) : $koUrl;

        $publicKey = Configuration::get('CLEARPAY_SANDBOX_PUBLIC_KEY');
        $secretKey = Configuration::get('CLEARPAY_SANDBOX_SECRET_KEY');
        $environment = Configuration::get('CLEARPAY_ENVIRONMENT');

        if ($environment === 'production') {
            $publicKey = Configuration::get('CLEARPAY_PRODUCTION_PUBLIC_KEY');
            $secretKey = Configuration::get('CLEARPAY_PRODUCTION_SECRET_KEY');
        }

        $okUrl = _PS_BASE_URL_SSL_.__PS_BASE_URI__
            .'index.php?canonical=true&fc=module&module=clearpay&controller=notify'
            .'&token='.$urlToken . '&' . http_build_query($query)
        ;

        \Afterpay\SDK\Model::setAutomaticValidationEnabled(true);
        $createCheckoutRequest = new CreateCheckout();
        $clearpayMerchantAccount = new ClearpayMerchantAccount();
        $countryCode = $this->getCountryCode();
        $clearpayMerchantAccount
            ->setMerchantId($publicKey)
            ->setSecretKey($secretKey)
            ->setApiEnvironment($environment)
        ;
        if (!is_null($countryCode)) {
            $clearpayMerchantAccount->setCountryCode($countryCode);
        }

        $createCheckoutRequest
            ->setMerchant(array(
                'redirectConfirmUrl' => $okUrl,
                'redirectCancelUrl' => $cancelUrl
            ))
            ->setMerchantAccount($clearpayMerchantAccount)
            ->setTotalAmount(
                Clearpay::parseAmount($cart->getOrderTotal(true, Cart::BOTH)),
                $currency
            )
            ->setTaxAmount(
                Clearpay::parseAmount(
                    $cart->getOrderTotal(true, Cart::BOTH) - $cart->getOrderTotal(false, Cart::BOTH)
                ),
                $currency
            )
            ->setConsumer(array(
                'phoneNumber' => $billingAddress->phone,
                'givenNames' => $customer->firstname,
                'surname' => $customer->lastname,
                'email' => $customer->email
            ))
            ->setBilling(array(
                'name' => $billingAddress->firstname . " " . $billingAddress->lastname,
                'line1' => $billingAddress->address1,
                'line2' => $billingAddress->address2,
                'suburb' => $billingAddress->city,
                'state' => $billingStateCode,
                'postcode' => $billingAddress->postcode,
                'countryCode' => $billingCountryCode,
                'phoneNumber' => $billingAddress->phone
            ))
            ->setShipping(array(
                'name' => $shippingAddress->firstname . " " . $shippingAddress->lastname,
                'line1' => $shippingAddress->address1,
                'line2' => $shippingAddress->address2,
                'suburb' => $shippingAddress->city,
                'state' => $shippingStateCode,
                'postcode' => $shippingAddress->postcode,
                'countryCode' => $shippingCountryCode,
                'phoneNumber' => $shippingAddress->phone
            ))
            ->setShippingAmount(
                Clearpay::parseAmount($cart->getTotalShippingCost()),
                $currency
            )
            ->setCourier(array(
                'shippedAt' => '',
                'name' => $carrier->name,
                'tracking' => '',
                'priority' => 'STANDARD'
            ));

        if (!empty($discountAmount)) {
            $createCheckoutRequest->setDiscounts(array(
                array(
                    'displayName' => 'Clearpay Discount coupon',
                    'amount' => array(Clearpay::parseAmount($discountAmount), $currency)
                )
            ));
        }

        $items = $cart->getProducts();
        $products = array();
        foreach ($items as $key => $item) {
            $products[] = array(
                'name' => $item['name'],
                'sku' => $item['reference'],
                'quantity' => $item['quantity'],
                'price' => array(
                    Clearpay::parseAmount($item['price_wt']),
                    $currency
                )
            );
        }
        $createCheckoutRequest->setItems($products);

        $header = $this->module->name . '/' . $this->module->version
            . '(Prestashop/' . _PS_VERSION_ . '; PHP/' . phpversion() . '; Merchant/' . $publicKey
            . ') ' . _PS_BASE_URL_SSL_.__PS_BASE_URI__;
        $createCheckoutRequest->addHeader('User-Agent', $header);

        $url = $cancelUrl;
        if ($createCheckoutRequest->isValid()) {
            $createCheckoutRequest->send();
            if (isset($createCheckoutRequest->getResponse()->getParsedBody()->errorCode)) {
                $this->saveLog($createCheckoutRequest->getResponse()->getParsedBody()->message, null, 2);
            } else {
                $url = $createCheckoutRequest->getResponse()->getParsedBody()->redirectCheckoutUrl;
                try {
                    $orderId = $createCheckoutRequest->getResponse()->getParsedBody()->token;
                    $sql = "INSERT INTO `" . _DB_PREFIX_ . "clearpay_order` (`id`, `order_id`, `token`)
                     VALUES ('$cart->id','$orderId', '$urlToken')";
                    $result = Db::getInstance()->execute($sql);
                    if (!$result) {
                        throw new \Exception('Unable to save clearpay-order-id in database: '. $sql);
                    }
                } catch (\Exception $exception) {
                    $this->saveLog($exception->getMessage(), 3);
                    $url = $cancelUrl;
                }
            }
        } else {
            $this->saveLog($createCheckoutRequest->getValidationErrors(), null, 2);
        }

        Tools::redirect($url);
    }

    /**
     * @param null $shippingAddress
     * @param null $billingAddress
     * @return mixed
     */
    private function getCountryCode()
    {
        $allowedCountries = unserialize(Clearpay::getExtraConfig('ALLOWED_COUNTRIES', null));
        $lang = Language::getLanguage($this->context->language->id);
        $langArray = explode("-", $lang['language_code']);
        if (count($langArray) != 2 && isset($lang['locale'])) {
            $langArray = explode("-", $lang['locale']);
        }
        $language = Tools::strtoupper($langArray[count($langArray)-1]);
        // Prevent null language detection
        if (in_array(Tools::strtolower($language), $allowedCountries)) {
            return $language;
        }

        $shippingAddress = new Address($cart->id_address_delivery);
        if ($shippingAddress) {
            $language = Country::getIsoById($shippingAddress->id_country);
            if (in_array(Tools::strtolower($language), $allowedCountries)) {
                return $language;
            }
        }
        $billingAddress = new Address($cart->id_address_invoice);
        if ($billingAddress) {
            $language = Country::getIsoById($billingAddress->id_country);
            if (in_array(Tools::strtolower($language), $allowedCountries)) {
                return $language;
            }
        }
        return null;
    }
}
