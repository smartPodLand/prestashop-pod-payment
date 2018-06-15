
<p class="payment_module">
    <a href="javascript:$('#podgate').submit();" title="{l s='Online payment with Pod' mod='podpayment'}">
        <img style="height: 80px" src="{$modules_dir}/podpay/views/img/paypod.png" />
        {l s='پرداخت با کیف پول پاد' mod='podpayment'}
        <br>
    </a></p>
<form action="module/podpay/pay?do=payment" method="post" id="podgate" class="hidden">
    <input type="hidden" name=/"{$orderId}" />
</form>
<br><br>
