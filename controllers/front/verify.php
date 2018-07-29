<?php
if(!isset($_SESSION))
{
session_start();
}
class podpayverifyModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		parent::initContent();
		//$billNumber        = Tools::getValue('billNumber');
		//$paymentBillNumber = Tools::getValue('paymentBillNumber');
		$invoiceId         = Tools::getValue('invoiceId');
		$url               = Configuration::get('POD_APIURL');
		$api_token         = Configuration::get('POD_APITOKEN');

		$fields = "/nzh/biz/getInvoiceList/?size=1&id={$invoiceId}&offset=0";
		$ch     = curl_init($url . $fields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"_token_: {$api_token}",
			"_token_issuer_: 1"
		]);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($ch);
		$e        = curl_error($ch);
		if ($e)
		{
			echo $e;
		}
		curl_close($ch);
		$resp = json_decode($response);
		if ($resp->result[0]->waiting)
		{
			$fields = "/nzh/biz/verifyInvoice/?id={$invoiceId}";
			$ch     = curl_init($url . $fields);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"_token_: {$api_token}",
				"_token_issuer_: 1"
			]);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($ch);
			$e        = curl_error($ch);
			if ($e)
			{
				echo $e;
			}
			curl_close($ch);
			$respVerify = json_decode($response);
		}
		if (isset($respVerify) && $respVerify->result->payed)
		{
			//فاکتور بسته می‌شود تا امکان درخواست تسویه حساب فعال شود
			// با توجه به سیاست کسب و کار خود برای کنسل کردن خرید این بخش را باید تغییر دهید.
			$fields = "/nzh/biz/closeInvoice/?id={$invoiceId}";
			$ch     = curl_init($url. $fields);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				"_token_: {$api_token}",
				"_token_issuer_: 1"
			]);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($ch);
			$e        = curl_error($ch);
			if ($e)
			{
				echo $e;
			}
			curl_close($ch);
			$respClose = json_decode($response);
			if ($respClose->hasError)
			{
				echo "خطا در پرداخت";
				echo "<pre>";
				print_r($resp);
				exit();
			}
			else
			{
				$context  = Context::getContext();
				$pay      = new PodPay;
				$cart     = $this->context->cart;
				$customer = new Customer((int) $cart->id_customer);
				$currency = $context->currency;
				$order    = $_SESSION['order'];
				$amount   = $_SESSION['amount'];
				$message  = "پرداخت انجام شد";

				$p = $pay->validateOrder((int) $order, _PS_OS_PAYMENT_, $amount, $pay->displayName, $message, array(), (int) $currency->id, false, $customer->secure_key);

				Tools::redirect('history.php');
			}
		}
	}
}