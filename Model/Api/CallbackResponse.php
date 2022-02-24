<?php
namespace Sparxpres\Websale\Model\Api;

use Sparxpres\Websale\Api\Data\CallbackResponseInterface;

class CallbackResponse implements CallbackResponseInterface
{
    private $success;
    private $message;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->success = false;
        $this->message = null;
    }

    /**
     * @api
     * @return bool
     */
    public function getSuccess() {
        return $this->success;
    }

    /**
     * @api
     * @param $value bool
     * @return null
     */
    public function setSuccess($value = false) {
        $this->success = $value;
    }

    /**
     * @api
     * @return string|null
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * @api
     * @param $value string
     * @return null
     */
    public function setMessage($value) {
        $this->message = $value;
    }

}