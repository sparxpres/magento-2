<?php
namespace Sparxpres\Websale\Controller\LaunchXpresPay;

class Index implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    protected $scopeConfig;
    protected $checkoutSession;
    protected $storeManager;
    protected $urlBuilder;
    protected $resultRedirectFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    public function execute()
    {
        $linkId = $this->scopeConfig->getValue(
            'payment/sparxpres_gateway/link_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        $order = $this->checkoutSession->getLastRealOrder();
        $incrementId = $order->getIncrementId();
        $amountCents = ceil($order->getGrandTotal() * 100);

        $returnUrl = $this->urlBuilder->getUrl('checkout/onepage/success/');
        $cancelUrl = $this->urlBuilder->getUrl('checkout/cart/');

        $redirect = $this->resultRedirectFactory->create();
        $redirect->setUrl(
            'https://sparxpres.dk/app/xprespay/betal/'
            .'?linkId='.$linkId
            .'&transactionId='.$incrementId
            .'&amountCents='.$amountCents
            .'&returnurl='.urlencode($returnUrl)
            .'&cancelurl='.urlencode($cancelUrl)
        );
        return $redirect;
    }
}
