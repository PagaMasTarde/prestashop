{*
 * This file is part of the official Paga+Tarde module for PrestaShop.
 *
 * @author    Paga+Tarde <soporte@pagamastarde.com>
 * @copyright 2015-2016 Paga+Tarde
 * @license   proprietary
 *}

<link rel="stylesheet" type="text/css" media="all" href="{$css|escape:'quotes'}">
{$confirmation|escape:'quotes'}
<div class="paylater-content-form">
    <section class="section">
        <div class="column-left">
            <h3><i class="icon icon-credit-card"></i> {l s='Paylater Configuration Panel' mod='paylater'}</h3>
            <a target="_blank" href="https://bo.pagamastarde.com" class="btn btn-default" title="Login al panel de Paga+Tarde"><i class="icon-user"></i> {l s='Paylater Backoffice Login' mod='paylater'}</a>
            <br><a target="_blank" href="http://docs.pagamastarde.com/" class="btn btn-default" title="Documentación"><i class="icon-book"></i> {l s='Paylater documentation' mod='paylater'}</a>
        </div>
        <div class="column-center">
            <p>
                {l s='Paylater configuration panel, please take your time to configure the payment method behavior' mod='paylater'}
            </p>
        </div>
        <div class="column-right">
            <img src="{$logo|escape:'quotes'}"/>
        </div>
    </section>
    {$form nofilter}
</div>
