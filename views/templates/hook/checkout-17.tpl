<script type="text/javascript" src="https://cdn.pagamastarde.com/pmt-js-client-sdk/3/js/client-sdk.min.js"></script>
{if $includeSimulator}
    <span class="js-pmt-payment-type"></span>
    <div class="PmtSimulator"
         data-pmt-num-quota="4" data-pmt-max-ins="12" data-pmt-style="blue"
         data-pmt-type="{$simulatorType}"
         data-pmt-discount="{$discount}" data-pmt-amount="{$amount}" data-pmt-expanded="yes">
    </div>
    <script type="text/javascript">
        (function(){
            pmtClient.setPublicKey('{$publicKey}');
            pmtClient.simulator.init();
        })();
    </script>
{/if}
<script type="text/javascript">
    pmtClient.events.send('checkout', { basketAmount: {$amount} } );
</script>
