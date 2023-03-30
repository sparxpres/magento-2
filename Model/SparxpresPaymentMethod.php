<?php
namespace Sparxpres\Websale\Model;

use Magento\Sales\Model\Order;

class SparxpresPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'sparxpres_payment';
    protected $_isOffline = true;

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return $this
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

}
