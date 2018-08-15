<?php
if(!defined('_PS_VERSION_')){
    exit;
}
if (!defined('ALL')){
	define('ALL', 4);
}
if(!defined('ONLY')){
	define('ONLY', 2);
}
class PodPay extends PaymentModule {

	private $_html = '';
	private $_postErrors = array();

	public function __construct() {

		$this->name = 'podpay';
		$this->tab = 'payments_gateways';
		$this->version = '1.1.0';
		$this->author = 'Mehran Rahbardar';
		$this->currencies = true;
		$this->currencies_mode = 'radio';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
        $this->bootstrap = true;
		parent::__construct();
		$this->displayName = $this->l('Pod Payment Module');
		$this->description = $this->l('Online Payment With Pod');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details?');

		if (!sizeof(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module');
	}
    public function install() {
		if (!parent::install() | !$this->registerHook('payment') || !$this->registerHook('paymentReturn'))
			return false;
		else
			return true;
	}
	public function uninstall() {
		if (!parent::uninstall())
			return false;
		else
			return true;
	}
public function hookPayment($params) {
		$pod_only = Configuration::get('POD_ONLY');

		$login_by_pod = $this->context->cookie->login_by_pod;

	if ($this->active){
		if($pod_only==ONLY){
			if($login_by_pod){
				return $this->display(__FILE__, 'podpayment.tpl');
			}
		}
		else if ($pod_only==ALL) {
			return $this->display(__FILE__, 'podpayment.tpl');
		}

	}
}

public function hookPaymentReturn($params) {

}

}
?>
