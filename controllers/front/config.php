<?php
/**
 * This file is part of the official Paylater module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2019 Paga+Tarde
 * @license   proprietary
 */

/**
 * Class PaylaterLogModuleFrontController
 */
class PaylaterConfigModuleFrontController extends ModuleFrontController
{
    /**
     * Initial method
     */
    public function initContent()
    {
        $this->authorize();
        $method = strtolower($_SERVER['REQUEST_METHOD']) . "Method";
        if (method_exists($this, $method)) {
            header('HTTP/1.1 200 Ok', true, 200);
            header('Content-Type: application/json', true);
            header('Content-Length: ' . Tools::strlen($result));
            echo json_encode($this->{$method}());
            exit();
        }
        header('HTTP/1.1 405 Method not allowed', true, 405);
        header('Content-Type: application/json', true);

        exit();
    }

    /**
     * Update POST params in DB
     */
    public function postMethod()
    {
        $errors = array();
        if (count($_POST)) {
            foreach ($_POST as $config => $value) {
                $defaultConfigs = json_decode(getenv('PMT_DEFAULT_CONFIGS'), true);
                if (isset($defaultConfigs[$config])) {
                    Db::getInstance()->update(
                        'pmt_config',
                        array('value' => $value),
                        'config = \''. $config .'\''
                    );
                } else {
                    $errors[$config] = $value;
                }
            }
        } else {
            $errors['NO_POST_DATA'] = 'No post data provided';
        }

        $dbConfigs = $this->getMethod();
        if (count($errors) > 0) {
            $dbConfigs['__ERRORS__'] = $errors;
        }
        return $dbConfigs;
    }

    /**
     * Read PTM configs
     *
     * @throws PrestaShopDatabaseException
     */
    public function getMethod()
    {
        $sql_content = 'select * from ' . _DB_PREFIX_. 'pmt_config';
        $dbConfigs = Db::getInstance()->executeS($sql_content);

        // Convert a multimple dimension array for SQL insert statements into a simple key/value
        $simpleDbConfigs = array();
        foreach ($dbConfigs as $config) {
            $simpleDbConfigs[$config['config']] = $config['value'];
        }
        return $simpleDbConfigs;
    }

    /**
     * @return bool|null
     */
    public function authorize()
    {
        $privateKey = Configuration::get('pmt_private_key');

        if (Tools::getValue('secret', false) == $privateKey) {
            return true;
        }

        header('HTTP/1.1 403 Forbidden', true, 403);
        header('Content-Type: application/json', true);

        exit();
    }
}
