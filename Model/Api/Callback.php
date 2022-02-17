<?php
namespace Sparxpres\Websale\Model\Api;

class Callback implements \Sparxpres\Websale\Api\CallbackInterface
{
    protected $request;
    protected $response;
    protected $orderRepository;

    /**
     * CustomerAddress constructor.
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(\Magento\Framework\App\RequestInterface $request,
                                \Magento\Framework\App\ResponseInterface $response,
                                \Magento\Sales\Api\OrderRepositoryInterface $orderRepository)
    {
        $this->request = $request;
        $this->response = $response;
        $this->orderRepository = $orderRepository;
    }

    /**
     * doPost method
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function updateOrderStatus() {
        $resp = $this->response;

        try {
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
            if ($originalStatus === 'canceled'
                || $originalStatus === 'fraud'
                || $originalStatus === 'closed'
                || $originalStatus === 'complete'
            ) {
                $order->addCommentToStatusHistory("Sparxpres sendte callback (".$status."), men ordrens status var ".$originalStatus.", og er derfor IKKE opdateret.");
                $order->save();

                $resp->setContent(json_encode([
                    'success' => true,
                    'message' => "Status NOT updated, because original status is: ".$originalStatus
                ]));
            } else {
                switch ($status) {
                    case "NEW":
                        $order->addCommentToStatusHistory("Sparxpres har modtaget låneansøgningen.", "pending_payment", false);
                        $order->save();
                        break;
                    case "WAITING_FOR_SIGNATURE":
                        $order->addCommentToStatusHistory("Sparxpres afventer kundens underskrift.", "pending_payment", false);
                        $order->save();
                        break;
                    case "REGRETTED":
                    case "CANCELED":
                    case "CANCELLED":
                        $order->addCommentToStatusHistory("Sparxpres har annulleret lånet.", "canceled", false);
                        $order->save();
                        break;
                    case "RESERVED":
                        $order->addCommentToStatusHistory("Lånet er klar til frigivelse hos Sparxpres.", "payment_review", false);
                        $order->save();
                        break;
                    case "CAPTURED":
                        $order->addCommentToStatusHistory("Lånet er sat til udbetaling hos Sparxpres.", "processing", false);
                        $order->save();
                        break;
                    case "DECLINE":
                        $order->addCommentToStatusHistory("Sparxpres har givet afslag på låneansøgningen.", "canceled", false);
                        $order->save();
                        break;
                    default:
                        throw new \InvalidArgumentException("Status not valid");
                }

                $resp->setContent(json_encode([
                    'success' => true,
                ]));
            }
        } catch(\Exception $e) {
            $resp->setContent(json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]));
        }

        return $resp->sendResponse();
    }

}