<?php
namespace Sparxpres\Websale\Model;

class XpresPayPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'xprespay-payment';
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
