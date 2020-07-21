{*
 * This file is part of the official Pagantis module for PrestaShop.
 *
 * @author    Pagantis <integrations@pagantis.com>
 * @copyright 2019 Pagantis
 * @license   proprietary
 *}
{if $PAGANTIS_IS_ENABLED}
    <div class="row">
        <div class="col-xs-12">
            <p class="payment_module Pagantis ps_version_{$PAGANTIS_PS_VERSION|escape:'htmlall':'UTF-8'}">
                <a class="pagantis-checkout pagantis-checkout-Pagantis ps_version_{$PAGANTIS_PS_VERSION|escape:'htmlall':'UTF-8'} locale_{$PAGANTIS_LOCALE|escape:'htmlall':'UTF-8'}" href="{$PAGANTIS_PAYMENT_URL|escape:'htmlall':'UTF-8'}" title="{$PAGANTIS_TITLE|escape:'htmlall':'UTF-8'}">
                    {if $PAGANTIS_PS_VERSION !== '1-7'}{$PAGANTIS_TITLE|escape:'quotes'}&nbsp;{/if}
                    <span class="pagantisSimulatorPagantis ps_version_{$PAGANTIS_PS_VERSION|escape:'htmlall':'UTF-8'}"></span>

                </a>
            </p>
            <script type="text/javascript">
                function checkSimulatorContentPagantis() {
                    var pgContainer = document.getElementsByClassName("pagantisSimulatorPagantis");
                    if(pgContainer.length > 0) {
                        var pgElement = pgContainer[0];
                        if (pgElement.innerHTML != '') {
                            return true;
                        }
                    }
                    return false;
                }

                function loadSimulatorPagantis()
                {
                    window.PSSimulatorAttemptsPagantis = window.PSSimulatorAttemptsPagantis + 1;
                    if (window.PSSimulatorAttemptsPagantis > 10 )
                    {
                        clearInterval(window.PSSimulatorIdPagantis);
                        return true;
                    }

                    if (checkSimulatorContentPagantis()) {
                        clearInterval(window.PSSimulatorIdPagantis);
                        return true;
                    }

                    if (typeof pgSDK == 'undefined') {
                        return false;
                    }
                    var sdk = pgSDK;

                    sdk.simulator.init({
                        type: {$PAGANTIS_SIMULATOR_DISPLAY_TYPE_CHECKOUT|escape:'javascript':'UTF-8'},
                        locale: '{$PAGANTIS_LOCALE|escape:'javascript':'UTF-8'}'.toLowerCase(),
                        country: '{$PAGANTIS_COUNTRY|escape:'javascript':'UTF-8'}'.toLowerCase(),
                        publicKey: '{$PAGANTIS_PUBLIC_KEY|escape:'javascript':'UTF-8'}',
                        selector: '.pagantisSimulatorPagantis',
                        numInstalments: '{$PAGANTIS_SIMULATOR_START_INSTALLMENTS|escape:'javascript':'UTF-8'}',
                        totalAmount: '{$PAGANTIS_AMOUNT|escape:'javascript':'UTF-8'}'.replace('.', ','),
                        totalPromotedAmount: '{$PAGANTIS_PROMOTED_AMOUNT|escape:'javascript':'UTF-8'}'.replace('.', ','),
                        amountParserConfig: {
                            thousandSeparator: '{$PAGANTIS_SIMULATOR_THOUSAND_SEPARATOR|escape:'javascript':'UTF-8'}',
                            decimalSeparator: '{$PAGANTIS_SIMULATOR_DECIMAL_SEPARATOR|escape:'javascript':'UTF-8'}',
                        }
                    });
                    return true;
                }
                window.PSSimulatorAttemptsPagantis = 0;
                if (!loadSimulatorPagantis()) {
                    window.PSSimulatorIdPagantis = setInterval(function () {
                        loadSimulatorPagantis();
                    }, 2000);
                }
            </script>
            <style>
                .pagantisSimulatorPagantis {
                    display: inline-block;
                }
                .pagantisSimulatorPagantis .mainImageLogo{
                    width: 20px;
                    height: 20px;
                }
                .pagantisSimulatorPagantis.ps_version_1-5 {
                    vertical-align: middle;
                    padding-top: 20px;
                    margin-left: 10px;
                }
                .pagantisSimulatorPagantis.ps_version_1-6 {
                    vertical-align: top;
                    margin-left: 20px;
                    margin-top: -5px;

                }
                .pagantisSimulatorPagantis.ps_version_1-7 {
                    padding-top: 0px;
                }
                p.payment_module.Pagantis.ps_version_1-5 {
                    min-height: 0px;
                }
                p.payment_module.Pagantis.ps_version_1-7 {
                    margin-left: -5px;
                    margin-top: -15px;
                    margin-bottom: 0px;
                }
                p.payment_module a.pagantis-checkout {
                    background: url(https://cdn.digitalorigin.com/assets/master/logos/pg-favicon.png) 5px 5px no-repeat #fbfbfb;
                    background-size: 80px;
                }
                p.payment_module a.pagantis-checkout.ps_version_1-7 {
                    background: none;
                }
                .payment-option img[src*='cdn.digitalorigin.com'] {
                    height: 18px;
                    padding-left: 5px;
                    content:url('https://cdn.digitalorigin.com/assets/master/logos/pg.png');

                }
                p.payment_module a.pagantis-checkout.ps_version_1-6 {
                    background-color: #fbfbfb;
                    max-height: 90px;
                }
                p.payment_module a.pagantis-checkout.ps_version_1-6:after {
                    display: block;
                    content: "\f054";
                    position: absolute;
                    right: 15px;
                    margin-top: -11px;
                    top: 50%;
                    font-family: "FontAwesome";
                    font-size: 25px;
                    height: 22px;
                    width: 14px;
                    color: #777;
                }
                p.payment_module a.pagantis-checkout.ps_version_1-5 {
                    height: 90px;
                    padding-left: 99px;
                }
                p.payment_module a:hover {
                    background-color: #f6f6f6;
                }
                p.payment_module.Pagantis.ps_version_1-5 {
                    min-height: 0px;
                    display: inline;
                }
                {$PAGANTIS_SIMULATOR_CSS_CHECKOUT_PAGE_STYLES|escape:'javascript':'UTF-8'}
            </style>
        </div>
    </div>
{/if}