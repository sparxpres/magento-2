<?php
namespace Sparxpres\Websale\Model;

class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod {
	protected $_code = 'sparxpres-payment';
	protected $_isOffline = true;

	public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount) {

	}

	public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount) {

	}

}
