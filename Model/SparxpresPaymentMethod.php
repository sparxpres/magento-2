<?php
namespace Sparxpres\Websale\Model;

class SparxpresPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'sparxpres-payment';
    protected $_isOffline = true;

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return void
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return void
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {

    }

}
