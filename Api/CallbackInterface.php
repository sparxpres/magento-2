<?php
namespace Sparxpres\Websale\Api;

interface CallbackInterface
{
    /**
     * Put/Post api
     *
     * @return \Sparxpres\Websale\Api\Data\CallbackResponseInterface
     */
    public function updateOrderStatus();
}
