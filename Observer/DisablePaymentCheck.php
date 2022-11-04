<?php
namespace Sparxpres\Websale\Observer;

class DisablePaymentCheck extends \Sparxpres\Websale\Block\SparxpresTemplate implements \Magento\Framework\Event\ObserverInterface
{
    private $price;
    private $logger;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->logger = $logger;
        parent::__construct($context, $objectManager, $data);
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $instance = $observer->getMethodInstance();
        $result = $observer->getResult();

        if ($instance->getCode() == 'sparxpres-payment') {
            $this->price = $observer->getQuote()->getGrandTotal();
            $result->setData(
                'is_available',
                !empty($this->getLinkId()) && $this->isActive() && $this->is_finance_enabled()
            );
        } elseif ($instance->getCode() == 'xprespay-payment') {
            $this->price = $observer->getQuote()->getGrandTotal();
            $result->setData(
                'is_available',
                !empty($this->getLinkId()) && $this->isActive() && $this->is_xprespay_enabled()
            );
        }
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
