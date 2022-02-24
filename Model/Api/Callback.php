<?php
namespace Sparxpres\Websale\Model\Api;

use Magento\Sales\Model\Order;

class Callback implements \Sparxpres\Websale\Api\CallbackInterface
{
    protected $request;
    protected $response;
    protected $orderRepository;
    protected $responseFactory;

    /**
     * CustomerAddress constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Sparxpres\Websale\Api\Data\CallbackResponseInterfaceFactory $responseFactory
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request,
                                \Magento\Framework\App\ResponseInterface $response,
                                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
                                \Sparxpres\Websale\Api\Data\CallbackResponseInterfaceFactory $responseFactory)
    {
        $this->request = $request;
        $this->response = $response;
        $this->orderRepository = $orderRepository;
        $this->responseFactory = $responseFactory;
    }

    /**
     * doPost method
     * @return \Sparxpres\Websale\Api\Data\CallbackResponseInterface
     */
    public function updateOrderStatus() {
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
            $amount = $params->amount ?? null;
            if (empty($status) || empty($transactionId)) {
                throw new \InvalidArgumentException("Invalid json content");
            }

            $order = $this->orderRepository->get($transactionId);
            $orderAmount = ceil($order->getGrandTotal());
            if (($status === 'NEW' || $status === 'WAITING_FOR_SIGNATURE' || $status === 'RESERVED' || $status === 'CAPTURED') && $orderAmount !== ceil($amount)) {
                throw new \InvalidArgumentException("Invalid amount");
            }

            $originalStatus = $order->getStatus();
            if ($originalStatus === Order::STATE_CANCELED
                || $originalStatus === Order::STATUS_FRAUD
                || $originalStatus === Order::STATE_CLOSED
                || $originalStatus === Order::STATE_COMPLETE
            ) {
                $order->addCommentToStatusHistory("Sparxpres sendte callback (".$status."), men ordrens status var ".$originalStatus.", og er derfor IKKE opdateret.");
                $order->save();

                $resp->setSuccess(true);
                $resp->setMessage("Status NOT updated, because original status is: ".$originalStatus);
            } else {
                switch ($status) {
                    case "NEW":
                        $order->addCommentToStatusHistory("Sparxpres har modtaget låneansøgningen.", Order::STATE_PENDING_PAYMENT, false);
                        $order->save();
                        break;
                    case "WAITING_FOR_SIGNATURE":
                        $order->addCommentToStatusHistory("Sparxpres afventer kundens underskrift.", Order::STATE_PENDING_PAYMENT, false);
                        $order->save();
                        break;
                    case "REGRETTED":
                    case "CANCELED":
                    case "CANCELLED":
                        $order->addCommentToStatusHistory("Sparxpres har annulleret lånet.", Order::STATE_CANCELED, false);
                        $order->save();
                        break;
                    case "RESERVED":
                        $order->addCommentToStatusHistory("Lånet er klar til frigivelse hos Sparxpres.", Order::STATE_PAYMENT_REVIEW, false);
                        $order->save();
                        break;
                    case "CAPTURED":
                        $order->addCommentToStatusHistory("Lånet er sat til udbetaling hos Sparxpres.", Order::STATE_PROCESSING, false);
                        $order->save();
                        break;
                    case "DECLINE":
                        $order->addCommentToStatusHistory("Sparxpres har givet afslag på låneansøgningen.", Order::STATE_CANCELED, false);
                        $order->save();
                        break;
                    default:
                        throw new \InvalidArgumentException("Status not valid");
                }
                $resp->setSuccess(true);
            }
            return $resp;
        } catch(\Exception $e) {
            throw new \Magento\Framework\Exception\ValidatorException(__($e->getMessage()));
        }
    }

}