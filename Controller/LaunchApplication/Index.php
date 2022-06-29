<?php
namespace Sparxpres\Websale\Controller\LaunchApplication;

class Index extends \Magento\Framework\App\Action\Action {
	protected $scopeConfig;
	protected $checkoutSession;
    protected $storeManager;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		parent::__construct($context);
		$this->scopeConfig = $scopeConfig;
		$this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
	}

	public function execute() {
        $linkId = $this->scopeConfig->getValue(
            'sparxpres/general/link_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

		$orderId = $this->checkoutSession->getLastOrderId();
		$order = $this->checkoutSession->getLastRealOrder();
        $amountCents = ceil($order->getGrandTotal() * 100);

		$returnUrl = $this->_url->getUrl('checkout/onepage/success/');
        $cancelUrl = $this->_url->getUrl('checkout/cart/');

		$redirect = $this->resultRedirectFactory->create();
		$redirect->setUrl(
            'https://sparxpres.dk/ansoegning/init/?linkId='.$linkId.
            '&transactionId='.$orderId.
            '&amountCents='.$amountCents.
            '&returnurl='.urlencode($returnUrl).
            '&cancelurl='.urlencode($cancelUrl)
        );
		return $redirect;
	}
}
