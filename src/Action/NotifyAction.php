<?php

namespace Strops\SyliusBorgunPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Reply\HttpResponse;
use Strops\SyliusBorgunPlugin\Bridge\OpenBorgunBridgeInterface;
use Payum\Core\Request\Notify;
use Webmozart\Assert\Assert;


final class NotifyAction implements ActionInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    private $api = [];

    /**
     * @var OpenBorgunBridgeInterface
     */
    private $openBorgunBridge;

    public function __construct(OpenBorgunBridgeInterface $openBorgunBridge)
    {
        $this->openBorgunBridge = $openBorgunBridge;
    }

    /**
     * @return \Payum\Core\GatewayInterface
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (!is_array($api)) {
            throw new UnsupportedApiException('Not supported.');
        }
        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Notify */
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        $model = $request->getModel();

        $this->openBorgunBridge->setAuthorizationDataApi(
            $this->api['environment'],
            $this->api['signature_key'],
            $this->api['pos_id']
        );

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $body = file_get_contents('php://input');
            $data = trim($body);

            try {
                $result = $this->openBorgunBridge->consumeNotification($data);

                if($result->getResponse()->order->order_id) {
                    $order = $this->openBorgunBridge->retrieve($result->getResponse()->order->order_id);

                    if(OpenBorgunBridgeInterface::SUCCESS_API_STATUS === $order->getStatus()) {
                        if(PaymentInterface::STATE_COMPLETED !== $payment->getState()) {
                            $status = $order->getResponse()->orders[0]->status;
                            $model['statusBorgun'] = $status;
                            $request->setMode($model);
                        }

                        throw new HttpResponse('SUCCESS');
                    }
                }
            } catch ( \OpenBorgun_Exception $e) {
                throw new HttpResponse($e->getMessage());
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Notify &&
            $request->getModel() instanceof \ArrayObject
            ;
    }
}