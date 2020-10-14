<?php
/**
 * This file is part of the official Clearpay module for PrestaShop.
 *
 * @author    Clearpay <integrations@clearpay.com>
 * @copyright 2020 Clearpay
 * @license   proprietary
 */

/**
 * Class ClearpayLogModuleFrontController
 */
class ClearpayConfigModuleFrontController extends ModuleFrontController
{
    /**
     * Initial method
     */
    public function initContent()
    {
        $this->authorize();
        $method = Tools::strtolower($_SERVER['REQUEST_METHOD']) . "Method";
        $params = (_PS_VERSION_ < 1.6) ? $_POST + $_GET : Tools::getAllValues();
        if (method_exists($this, $method)) {
            header('HTTP/1.1 200 Ok', true, 200);
            header('Content-Type: application/json', true);
            $result = json_encode($this->{$method}($params['product']));
            header('Content-Length: ' . Tools::strlen($result));
            echo $result;
            exit();
        }
        header('HTTP/1.1 405 Method not allowed', true, 405);
        header('Content-Type: application/json', true);

        exit();
    }

    /**
     * @param null $product
     * @return array
     */
    public function getExtraConfigs($product = null)
    {
        $availableProductsSQL = 'select * from ' . _DB_PREFIX_. 'clearpay_config where config = \'PRODUCTS\'';
        $dbProducts = Db::getInstance()->executeS($availableProductsSQL);
        $availableProductsArray = explode(',', $dbProducts[0]['value']);
        $unrequestedProducts = array_diff($availableProductsArray, array($product));
        $unrequestedProductSQL = '';
        foreach ($unrequestedProducts as $unrequestedProduct) {
            $unrequestedProductSQL .= "'". $unrequestedProduct . "',";
        }
        $unrequestedProductSQL = rtrim($unrequestedProductSQL, ",");
        $sql_content = 'select * from ' . _DB_PREFIX_.
            'clearpay_config where config not in (' . $unrequestedProductSQL . ') 
             and config not like (\'CLEARPAY_%\')  and config not like (\'PMT_%\') ';

        $dbConfigs = Db::getInstance()->executeS($sql_content);

        $simpleDbConfigs = array();
        foreach ($dbConfigs as $config) {
            $productConfigs = json_decode($config['value'], true);
            if ($productConfigs) {
                $simpleDbConfigs = array_merge($simpleDbConfigs, $productConfigs);
            }
            $simpleDbConfigs[$config['config']] = $config['value'];
        }
        unset($simpleDbConfigs[$product]);
        return $simpleDbConfigs;
    }

    /**
     * Update POST params in DB
     */
    public function postMethod()
    {

        $errors = array();
        $params = (_PS_VERSION_ < 1.6) ? $_POST + $_GET : Tools::getAllValues();
        unset($params['fc']);
        unset($params['module']);
        unset($params['controller']);
        unset($params['secret']);
        $product = $params['product'];
        unset($params['product']);
        $productConfigsSQL = 'select * from ' . _DB_PREFIX_.
            'clearpay_config where config = \''. pSQL($product) . '\'';
        $productConfigs = Db::getInstance()->executeS($productConfigsSQL);
        $availableProductsArray = json_decode($productConfigs[0]['value'], true);
        if (count($params) > 0) {
            foreach ($params as $config => $value) {
                if (array_key_exists($config, $availableProductsArray)) {
                    $availableProductsArray[$config] = $value;
                } else {
                    $defaultConfigs = $this->getExtraConfigs($product);
                    if (isset($defaultConfigs[$config])) {
                        if ($config !== 'product') {
                            Db::getInstance()->update(
                                'clearpay_config',
                                array('value' => pSQL($value)),
                                'config = \''. pSQL($config) .'\''
                            );
                        }
                    } else {
                        $errors[$config] = $value;
                    }
                }
                Db::getInstance()->update(
                    'clearpay_config',
                    array('value' => json_encode($availableProductsArray)),
                    'config = \''. pSQL($product) .'\''
                );
            }
        } else {
            $errors['NO_POST_DATA'] = 'No post data provided';
        }

        $dbConfigs = $this->getMethod($product);
        if (count($errors) > 0) {
            $dbConfigs['__ERRORS__'] = $errors;
        }
        return $dbConfigs;
    }

    /**
     * PTM configs
     *
     * @param null $product
     * @return array
     */
    public function getMethod($product = null)
    {
        return $this->getExtraConfigs($product);
    }

    /**
     * @return bool|null
     */
    public function authorize()
    {
        $productCode = Tools::getValue('product', false);
        $products = explode(',', Clearpay::getExtraConfig('PRODUCTS', null));
        $secretKey = Configuration::get('clearpay_private_key');
        $secretKeyGet = Tools::getValue('secret', false);
        if (!empty($secretKeyGet) && $secretKeyGet === $secretKey && in_array(Tools::strtoupper($productCode), $products)) {
            return true;
        }

        header('HTTP/1.1 403 Forbidden', true, 403);
        header('Content-Type: application/json', true);

        exit();
    }
}
