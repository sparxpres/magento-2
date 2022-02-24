<?php
namespace Sparxpres\Websale\Api\Data;

interface CallbackResponseInterface
{
    /**
     * Get the success state.
     *
     * @api
     * @return bool The success state.
     */
    public function getSuccess();

    /**
     * Set the success state.
     *
     * @api
     * @param $value bool The new success state.
     * @return null
     */
    public function setSuccess($value = false);

    /**
     * Get the message.
     *
     * @api
     * @return string|null The message.
     */
    public function getMessage();

    /**
     * Set the message.
     *
     * @api
     * @param $value string The new message.
     * @return null
     */
    public function setMessage($value);

}