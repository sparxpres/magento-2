<?php

namespace Sparxpres\Websale\Observer;

use Magento\Sales\Model\Order;

class SalesOrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            if ($order instanceof \Magento\Framework\Model\AbstractModel) {
                $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
                if ($paymentMethod == \Sparxpres\Websale\Model\SparxpresPaymentMethod::PAYMENT_METHOD_CODE ||
                    $paymentMethod == \Sparxpres\Websale\Model\XpresPayPaymentMethod::PAYMENT_METHOD_CODE) {
                    $order->setState(Order::STATE_PENDING_PAYMENT)->setStatus(Order::STATE_PENDING_PAYMENT);
                    $order->save();
                }
            }
        } catch (\Exception $e) {
            // ignored
        }

        return $this;
    }

}
