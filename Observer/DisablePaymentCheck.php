<?php
namespace Sparxpres\Websale\Observer;

class DisablePaymentCheck extends \Sparxpres\Websale\Block\SparxpresTemplate implements \Magento\Framework\Event\ObserverInterface
{
    private $price;
    private $logger;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->logger = $logger;
        parent::__construct($context, $registry, $data);
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $paymentMethod = $observer->getMethodInstance()->getCode();
            $result = $observer->getResult();

            if ($paymentMethod == 'sparxpres_payment') {
                $this->price = $observer->getQuote()->getGrandTotal();
                $result->setData(
                    'is_available',
                    !empty($this->getLinkId()) && $this->isActive() && $this->is_finance_enabled()
                );
            } elseif ($paymentMethod == 'xprespay_payment') {
                $this->price = $observer->getQuote()->getGrandTotal();
                $result->setData(
                    'is_available',
                    !empty($this->getLinkId()) && $this->isActive() && $this->is_xprespay_enabled()
                );
            }
        } catch (\Exception $e) {
            // ignored
        }

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getModuleVersion()
    {
        return null;
    }

}
