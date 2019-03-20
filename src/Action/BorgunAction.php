<?php

namespace Strops\SyliusBorgunPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;
use Strops\SyliusBorgunPlugin\Bridge\OpenBorgunBridgeInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Sylius\Component\Core\Model\CustomerInterface;
use Webmozart\Assert\Assert;

final class BorgunAction implements ApiAwareInterface, ActionInterface
{

    private $api = [];

    /** @var OpenBorgunBridgeInterface */
    private $openBorgunBridge;

    /**
     * @var Payum
     */
    private $payum;

    /**
     * BorgunAction constructor.
     * @param OpenBorgunBridgeInterface $openBorgunBridge
     * @param Payum $payum
     */
    public function __construct(OpenBorgunBridgeInterface $openBorgunBridge, Payum $payum)
    {
        $this->payum = $payum;
        $this->openBorgunBridge = $openBorgunBridge;
    }

    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $environment = $this->api['environment'];
        $signature = $this->api['signature_key'];
        $posId = $this->api['pos_id'];

        $openBorgun = $this->getOpenBorgunBridge();
        $openBorgun->setAuthorizationDataApi($environment, $signature, $posId);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if(null !== $model['orderId']) {
            $response = $openBorgun->retrieve($model['orderId'])->getResponse();
            Assert::keyExists($response->orders,0);

            if(OpenBorgunBridgeInterface::SUCCESS_API_STATUS === $response->status->statusCode) {
                $model['statusBorgun'] = $response->orders[0]->status;
                $request->setModel($model);
            }

            if(OpenBorgunBridgeInterface::NEW_API_STATUS !== $response->orders[0]->status) {

                return;
            }
        }

        /**
         * @var TokenInterface $token
         */
        $token = $request->getToken();
        $order = $this->prepareOrder($token,$model,$posId);
        $response = $openBorgun->create($order)->getResponse();

        if($response && OpenBorgunBridgeInterface::SUCCESS_API_STATUS === $response->status->statusCode) {
            $model['orderId'] = $response->orderId;
            $request->setModel($model);

            throw new HttpRedirect($response->redirectUri);
        }

        throw BorgunException::newInstance($response->status);
    }

    public function supports($request)
    {
        return
            $request instanceof SetBorgun &&
            $request->getModel() instanceof \ArrayObject
            ;
    }

    public function prepareOrder(TokenInterface $token, $model, $posId)
    {
        $notifyToken = $this->createNotifyToken($token->getGatewayName(), $token->getDetails());

        $order = [];
        $order['returnurlsuccess'] = $token->getTargetUrl();
        $order['returnurlcancel'] = $notifyToken->getTargetUrl();
        $order['customerip'] = $model['customerIp'];
        $order['merchantid'] = $posId;
        $order['currency'] = $model['currencyCode'];
        $order['checkhash'] = $this->getCheckHash();
        $order['language'] = $model['locale'];

        /** @var CustomerInterface $customer */
        $customer = $model['customer'];

        Assert::isInstanceOf(
            $customer,
            CustomerInterface::class,
            sprintf(
                'Make sure the first model is the %s',
                CustomerInterface::class
            )
        );

        $order['buyername'] = (string) $customer->getFirstName().' '.(string) $customer->getLastName();
        $order['buyeremail'] = $customer->getEmail();

        $order = $this->resolveProducts($model, $order);

        return $order;

    }

    public function getCheckHash($model)
    {
        $secretKey = 0;
        $message = utf8_encode("{$model['orderId']}|{$model['totalAmount']}|{$model['currencyCode']}");
        $checkHash = hash_hmac('sha256', $message, $secretKey);
        return $checkHash;

    }

    public function resolveProducts($model, $order)
    {
        if (!array_key_exists('products', $model) || count($model['products']) === 0) {
            return [
                [
                    'name' => $model['description'],
                    'unitPrice' => $model['totalAmount'],
                    'quantity' => 1
                ]
            ];
        }

        foreach($model['products'] as $key => $product)
        {
            $order['itemdescription_'.$key] = $product['description'];
            $order['itemcount_'.$key] = 1;
            $order['itemunitamount_'.$key] = $product['$totalAmount'];
            $order['itemamount_'.$key] = 1;
        }

        return $order;
    }

    /**
     * @param string $gatewayName
     * @param object $model
     *
     * @return TokenInterface
     */
    private function createNotifyToken($gatewayName, $model)
    {
        return $this->payum->getTokenFactory()->createNotifyToken(
            $gatewayName,
            $model
        );
    }


    public function setApi($api)
    {
        if(!is_array($api)) {
            throw new UnsupportedApiException('Not supported');
        }

        $this->api = $api;
    }

    /**
     * @return OpenBorgunBridgeInterface
     */
    public function getOpenBorgunBridge()
    {
        return $this->openBorgunBridge;
    }
    /**
     * @param OpenBorgunBridgeInterface $openBorgunBridge
     */
    public function setOpenPayUBridge($openBorgunBridge)
    {
        $this->openBorgunBridge = $openBorgunBridge;
    }
}