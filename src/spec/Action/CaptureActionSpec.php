<?php

namespace spec\Strops\SyliusBorgunPlugin\Action;

use Strops\SyliusBorgunPlugin\Action\CaptureAction;
use Strops\SyliusBorgunPlugin\SetBorgun;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

final class CaptureActionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(CaptureAction::class);
    }
    function it_executes(
        Capture $request,
        ArrayObject $model,
        \Iterator $iterator,
        OrderItemInterface $orderItem,
        OrderInterface $order,
        CustomerInterface $customer,
        TokenInterface $token,
        SetBorgun $setPayU,
        GatewayInterface $gateway
    )
    {
        $request->getModel()->willReturn($model);
        $model->getIterator()->willReturn($iterator);
        $model->offsetSet('customer', $customer)->willReturn(null);
        $model->offsetSet('locale', 'en')->willReturn(null);
        $request->getFirstModel()->willReturn($orderItem);
        $orderItem->getOrder()->willReturn($order);
        $order->getCustomer()->willReturn($customer);
        $order->getLocaleCode()->willReturn('en_US');
        $request->getToken()->willReturn($token);
        $setPayU->getToken()->willReturn($token);
        $setPayU->getModel()->willReturn($model);
        $this->setGateway($gateway);
        $this->getGateway()->execute($setPayU);
    }
    function it_throws_exception_when_model_is_not_array_object(Capture $request)
    {
        $request->getModel()->willReturn(null);
        $this
            ->shouldThrow(RequestNotSupportedException::class)
            ->during('execute', [$request])
        ;
    }
}