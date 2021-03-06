<?php

namespace spec\Strops\SyliusBorgunPlugin\Action;
use Strops\SyliusBorgunPlugin\Action\NotifyAction;
use Strops\SyliusBorgunPlugin\Bridge\OpenBorgunBridgeInterface;
use Strops\SyliusBorgunPlugin\SetBorgun;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
/**
 * @author Mikołaj Król <mikolaj.krol@bitbag.pl>
 */
final class NotifyActionSpec extends ObjectBehavior
{
    function let(OpenBorgunBridgeInterface $openBorgunBridge)
    {
        $this->beConstructedWith($openBorgunBridge);
    }
    function it_is_initializable()
    {
        $this->shouldHaveType(NotifyAction::class);
    }
    function it_executes(
        Notify $request,
        TokenInterface $token,
        ArrayObject $model,
        SetBorgun $setBorgun,
        GetHumanStatus $status,
        GatewayInterface $gateway
    )
    {
        $request->getToken()->willReturn($token);
        $request->getModel()->willReturn($model);
        $setBorgun->getToken()->willReturn($token);
        $setBorgun->getModel()->willReturn($model);
        $this->setGateway($gateway);
        $this->getGateway()->execute($status);
        $this->getGateway()->execute($setBorgun);
    }
    function it_throws_exception_when_model_is_not_array_object(Notify $request)
    {
        $request->getModel()->willReturn(null);
        $this
            ->shouldThrow(RequestNotSupportedException::class)
            ->during('execute', [$request])
        ;
    }
}