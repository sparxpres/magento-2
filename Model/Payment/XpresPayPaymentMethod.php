<?php
namespace Sparxpres\Websale\Model\Payment;

/**
 * See for how Magento has made it:
 * https://github.com/magento/magento2/blob/2.4.8/app/code/Magento/OfflinePayments/Model/Banktransfer.php
 */
class XpresPayPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    const PAYMENT_METHOD_CODE = 'xprespay_payment';
    protected $_code = self::PAYMENT_METHOD_CODE;
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

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        $instructions = $this->getConfigData('instructions');
        return $instructions !== null ? trim($instructions) : '';
    }

}
