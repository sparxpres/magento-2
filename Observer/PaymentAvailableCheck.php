<?php

namespace Sparxpres\Websale\Observer;

class PaymentAvailableCheck extends \Sparxpres\Websale\Block\SparxpresTemplate implements \Magento\Framework\Event\ObserverInterface
{
    private $price;
    private $checkoutSession;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry             $registry,
        \Magento\Checkout\Model\Session         $checkoutSession,
        \Psr\Log\LoggerInterface                $logger,
        array                                   $data = []
    )
    {
        $this->_logger = $logger;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $registry, $data);
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $paymentMethod = $observer->getEvent()->getMethodInstance()->getCode();
            if ($paymentMethod == \Sparxpres\Websale\Model\SparxpresPaymentMethod::PAYMENT_METHOD_CODE) {
                $this->price = $this->checkoutSession->getQuote()->getGrandTotal();

                $result = $observer->getEvent()->getResult();
                $result->setData(
                    'is_available',
                    $this->is_finance_enabled()
                );
            } elseif ($paymentMethod == \Sparxpres\Websale\Model\XpresPayPaymentMethod::PAYMENT_METHOD_CODE) {
                $this->price = $this->checkoutSession->getQuote()->getGrandTotal();

                $result = $observer->getEvent()->getResult();
                $result->setData(
                    'is_available',
                    $this->is_xprespay_enabled()
                );
            }
        } catch (\Exception $e) {
            // ignored
        }
    }

    /**
     * @Implement abstract method in SparxpresTemplate
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @Implement abstract method in SparxpresTemplate
     */
    public function getModuleVersion()
    {
        return null;
    }

}
