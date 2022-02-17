<?php
namespace Sparxpres\Websale\Controller\LaunchApplication;

class Index extends \Magento\Framework\App\Action\Action {
	protected $_scopeConfig;
	protected $_checkoutSession;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Checkout\Model\Session $checkoutSession
	) {
		parent::__construct($context);
		$this->_scopeConfig = $scopeConfig;
		$this->_checkoutSession = $checkoutSession;
	}

	public function execute() {
		$linkId = $this->_scopeConfig->getValue('sparxpres/general/link_id');
		$orderId = $this->_checkoutSession->getLastOrderId();

		$order = $this->_checkoutSession->getLastRealOrder();
		$amount = ceil($order->getGrandTotal());

		$returnUrl = $this->_url->getUrl('checkout/onepage/success/');

		$redirect = $this->resultRedirectFactory->create();
		$redirect->setUrl('https://sparxpres.dk/ansoegning/?linkId='.$linkId.'&transactionId='.$orderId.'&amount='.$amount.'&returnurl='.urlencode($returnUrl));
		return $redirect;
	}
}