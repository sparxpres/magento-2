<?php
namespace Sparxpres\Websale\Model\Api;

use Magento\Store\Model\ScopeInterface;

class Callback implements \Sparxpres\Websale\Api\CallbackInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var CallbackResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * CustomerAddress constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Sparxpres\Websale\Api\Data\CallbackResponseInterfaceFactory $responseFactory
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Sparxpres\Websale\Api\Data\CallbackResponseInterfaceFactory $responseFactory,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->orderRepository = $orderRepository;
        $this->responseFactory = $responseFactory;
        $this->orderSender     = $orderSender;
        $this->_scopeConfig    = $scopeConfig;
    }

    /**
     * doPost method
     * @return \Sparxpres\Websale\Api\Data\CallbackResponseInterface
     */
    public function updateOrderStatus()
    {
        try {
            // @var \Sparxpres\Websale\Api\Data\CallbackResponseInterface
            $resp = $this->responseFactory->create();

            $rawContent = $this->request->getContent();
            $params = json_decode($rawContent);

            if (empty($rawContent) || empty($params)) {
                throw new \InvalidArgumentException("Could not process body json");
            }

            $status = $params->status ?? null;
            $transactionId = $params->transactionId ?? null;
            $cbAmount = ceil($params->amount ?? 0);
            $cbAmountCents = ceil($params->amountCents ?? 0);
            if (empty($status) || empty($transactionId)) {
                throw new \InvalidArgumentException("Invalid json content");
            }

            $order = $this->orderRepository->get($transactionId);
            if (empty($order)) {
                throw new \InvalidArgumentException('Invalid order');
            }

            if ($status === 'NEW'
                || $status === 'WAITING_FOR_SIGNATURE'
                || $status === 'RESERVED'
                || $status === 'CAPTURED'
            ) {
                if ($cbAmountCents > 0) {
                    $orderAmtCents = ceil($order->getGrandTotal() * 100);
                    if ($orderAmtCents < $cbAmountCents - 10 || $orderAmtCents > $cbAmountCents + 10) {
                        // more than +/- 10 cents diff
                        throw new \InvalidArgumentException(
                            'Invalid amount (expected: '.$cbAmountCents.', was: '.$orderAmtCents.')'
                        );
                    }
                } else {
                    $orderAmount = ceil($order->getGrandTotal());
                    if ($orderAmount < $cbAmount - 1 || $orderAmount > $cbAmount + 1) {
                        // more thant +/- 1 kr diff
                        throw new \InvalidArgumentException(
                            'Invalid amount (expected: '.$cbAmount.', was: '.$orderAmount.')'
                        );
                    }
                }
            }

            $originalStatus = $order->getStatus();
            if ($originalStatus === \Magento\Sales\Model\Order::STATE_CANCELED
                || $originalStatus === \Magento\Sales\Model\Order::STATUS_FRAUD
                || $originalStatus === \Magento\Sales\Model\Order::STATE_CLOSED
                || $originalStatus === \Magento\Sales\Model\Order::STATE_COMPLETE
            ) {
                $order->addCommentToStatusHistory(
                    "Sparxpres sendte callback (".$status."), men ordrens status var "
                    .$originalStatus.", og er derfor IKKE opdateret."
                );
                $order->save();

                $resp->setSuccess(true);
                $resp->setMessage("Status NOT updated, because original status is: ".$originalStatus);
            } else {
                switch ($status) {
                    case "NEW":
                        $order->setStatus(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
                        $order->addStatusHistoryComment('Sparxpres har modtaget låneansøgningen.');
                        $order->save();
                        break;
                    case "WAITING_FOR_SIGNATURE":
                        $order->setStatus(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW);
                        $order->addStatusHistoryComment('Sparxpres afventer kundens underskrift.');
                        $order->save();
                        break;
                    case "REGRETTED":
                    case "CANCELED":
                    case "CANCELLED":
                        $order->cancel();
                        $order->addStatusHistoryComment('Sparxpres har annulleret lånet.');
                        $order->save();
                        break;
                    case "DECLINE":
                        $order->cancel();
                        $order->addStatusHistoryComment('Sparxpres har givet afslag på låneansøgningen.');
                        $order->save();
                        break;
                    case "RESERVED":
                        $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                        $order->addStatusHistoryComment('Lånet er klar til frigivelse hos Sparxpres.');
                        if ($this->getOrderEmailTriggerStatus() == 'reserved') {
                            $this->orderSender->send($order);
                        }
                        $order->save();
                        break;
                    case "CAPTURED":
                        $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
                        $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                        $order->addStatusHistoryComment('Lånet er sat til udbetaling hos Sparxpres.');
                        if ($this->getOrderEmailTriggerStatus() == 'captured') {
                            $this->orderSender->send($order);
                        }
                        $order->save();
                        break;
                    default:
                        throw new \InvalidArgumentException("Status not valid");
                }
                $resp->setSuccess(true);
            }
            return $resp;
        } catch (\Exception $e) {
            $resp = $this->responseFactory->create();
            $resp->setSuccess(false);
            $resp->setMessage($e->getMessage());
            return $resp;
        }
    }
    
    /**
     * Get Order Email Trigger Status
     * 
     * @return string
     */
    protected function getOrderEmailTriggerStatus()
    {
        return $this->_scopeConfig->getValue(
            'payment/sparxpres_payment/order_confirmation_email',
             ScopeInterface::SCOPE_STORE
        );
    }

}
