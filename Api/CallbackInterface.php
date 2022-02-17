<?php
namespace Sparxpres\Websale\Api;

interface CallbackInterface
{
    /**
     * Post api
     * @return \Magento\Framework\Controller\ResultInterface;
     */
    public function updateOrderStatus();
}
