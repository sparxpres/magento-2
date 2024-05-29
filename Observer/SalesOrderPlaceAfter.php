<?php
namespace Sparxpres\Websale\Observer;

use Sparxpres\Websale\Model\SparxpresPaymentMethod;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Sparxpres\Websale\Model\XpresPayPaymentMethod;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
            if ($paymentMethod == SparxpresPaymentMethod::PAYMENT_METHOD_CODE ||
                $paymentMethod == XpresPayPaymentMethod::PAYMENT_METHOD_CODE) {
                $order->setCanSendNewEmailFlag(false);
                $this->orderRepository->save($order);
            }
        } catch (\Exception $e) {
             // ignored
        }

        return $this;
    }
}
