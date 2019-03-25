<?php

namespace spec\Strops\SyliusBorgunPlugin\Action;
use Strops\SyliusBorgunPlugin\Action\BorgunAction;
use Strops\SyliusBorgunPlugin\Bridge\OpenBorgunBridge;
use Strops\SyliusBorgunPlugin\Bridge\OpenBorgunBridgeInterface;
use Strops\SyliusBorgunPlugin\SetBorgun;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Core\Model\CustomerInterface;
/**
 * @author Mikołaj Król <mikolaj.krol@bitbag.pl>
 * @author Patryk Drapik <patryk.drapik@bitbag.pl>
 */
final class BorgunActionSpec extends ObjectBehavior
{
    function let(OpenBorgunBridgeInterface $openPayUBridge, Payum $payum)
    {
        $this->beConstructedWith($openPayUBridge, $payum);
        $this->setApi(['environment' => 'secure', 'signature_key' => '123', 'pos_id' => '123']);
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(BorgunAction::class);
    }
    function it_executes(
        SetBorgun $request,
        TokenInterface $token,
        CustomerInterface $customer,
        ArrayObject $model,
        OpenBorgunBridgeInterface $openPayUBridge,
        \OpenBorgun_Result $openPayUResult,
        Payum $payum,
        GenericTokenFactoryInterface $tokenFactory
    )
    {
        $model->offsetGet('orderId')->willReturn(null);
        $model->offsetGet('customerIp')->willReturn(null);
        $model->offsetGet('description')->willReturn(null);
        $model->offsetGet('currencyCode')->willReturn(null);
        $model->offsetGet('totalAmount')->willReturn(null);
        $model->offsetGet('extOrderId')->willReturn(null);
        $model->offsetSet('orderId', 1)->shouldBeCalled();
        $model->offsetGet('customer')->willReturn($customer);
        $model->offsetGet('locale')->willReturn(null);
        $payum->getTokenFactory()->willReturn($tokenFactory);
        $tokenFactory->createNotifyToken(Argument::any(), Argument::any())->willReturn($token);
        $openPayUResult->getResponse()->willReturn((object)['status' => (object)['statusCode' => OpenBorgunBridgeInterface::SUCCESS_API_STATUS], 'orderId' => 1, 'redirectUri' => '/']);
        $openPayUBridge->setAuthorizationDataApi('secure', '123', '123')->shouldBeCalled();
        $dataApi = [
            'continueUrl' => null,
            'notifyUrl' => null,
            'customerIp' => null,
            'merchantPosId' => '123',
            'description' => null,
            'currencyCode' => null,
            'totalAmount' => null,
            'extOrderId' => null,
            'buyer' => [
                'email' => '',
                'firstName' => '',
                'lastName' => '',
                'language' => '',
            ],
            'products' => [
                [
                    'name' => null,
                    'unitPrice' => null,
                    'quantity' => 1
                ]
            ]
        ];
        $openPayUBridge->create($dataApi)->willReturn($openPayUResult);
        $request->getModel()->willReturn($model);
        $request->getToken()->willReturn($token);
        $request->getFirstModel()->willReturn($customer);
        $request->setModel($model)->shouldBeCalled();
        $this
            ->shouldThrow(HttpRedirect::class)
            ->during('execute', [$request])
        ;
    }
    function it_throws_exception_when_model_is_not_array_object(SetBorgun $request)
    {
        $request->getModel()->willReturn(null);
        $this
            ->shouldThrow(RequestNotSupportedException::class)
            ->during('execute', [$request])
        ;
    }
}