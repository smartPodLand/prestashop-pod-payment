<?php
if(!isset($_SESSION))
{
session_start();
}
class podpaypayModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $code = Tools::getValue('do');
        $url = Configuration::get('POD_APIURL');
        $api_token = Configuration::get('POD_APITOKEN');
        $guild = Configuration::get('GUILD_CODE');
        $ott = $this->getOtt();
        $cart = $this->context->cart;

        $amount   = floatval(number_format($cart->getOrderTotal(true, 3), 0, '.', ''));
       // $address  = new Address(intval($cart->id_address_invoice));

       // $products = $cart->getProducts(true);
        $fields = "/nzh/biz/issueInvoice?bizId={$_SESSION['bizId']}&userId={$_SESSION['userId']}&pay=true&preferredTaxRate=0&verificationNeeded=true&productId[]=0&price[]={$amount}&quantity[]=1&productDescription[]=خرید&guildCode={$guild}";

        //$fields = "/nzh/biz/issueInvoice?bizId={$_SESSION['bizId']}&userId={$_SESSION['userId']}&pay=true&postalCode=000000000&city=Tehran&state=test&address=test&preferredTaxRate=0&guildCode=INFORMATION_TECHNOLOGY_GUILD&verificationNeeded=true&";
        $total_price = 0;
//        foreach ($products as $product){
//            $price = ceil($product['total']/ $product['quantity']);
//            $total_price += ($price*$product['quantity']);
//            $name = urlencode($product['name']);
//            $fields .=  "productId[]=0&price[]={$price}&quantity[]={$product['quantity']}&productDescription[]={$name}&";
//        }
//        $delivery_price = $amount - $total_price;
//        $title = urlencode("هزینه ارسال");
//        $fields .=  "productId[]=0&price[]={$delivery_price}&quantity[]=1&productDescription[]={$title}";
        $ch = curl_init($url.$fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "_token_: {$api_token}",
            "_ott_: {$ott}",
            "_token_issuer_: 1"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);

        $e = curl_error($ch);
        if($e){
            echo $e;
        }
        curl_close($ch);
        $resp = json_decode($response);
        $_SESSION['invoice_id'] = $resp->result->id;
        $_SESSION['order']= $cart->id;
        $_SESSION['amount'] = $cart->getOrderTotal(true, 3);
        $url =  Configuration::get('POD_INVOICE_URL')."/v1/pbc/payinvoice/?invoiceId={$_SESSION['invoice_id']}&redirectUri={$this->context->link->getModuleLink('podpay', 'verify')}&callUri={$this->context->link->getModuleLink('podpayment', 'call')}";
        Tools::redirect($url);

    }
    function getOtt(){
        $url = Configuration::get('POD_APIURL'). "/nzh/ott/";
        $api_token = Configuration::get('POD_APITOKEN');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "_token_: {$api_token}",
            "_token_issuer_: 1"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $resp = json_decode($response);
        return $ott = $resp->ott;
    }
}