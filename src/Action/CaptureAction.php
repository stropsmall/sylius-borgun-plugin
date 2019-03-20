<?php

/**
 * This file was created by the developers from Strops.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 */

namespace Strops\SyliusBorgunPlugin\Action;

use Payum\Core\GatewayInterface;
use Strops\SyliusBorgunPlugin\SetBorgun;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Security\TokenInterface;

/**
 * Class CaptureAction
 * @package Strops\SyliusBorgunPlugin\Action
 * @author Lubos Beran <l.beran@hotmail.sk>
 */
final class CaptureAction implements ActionInterface, GatewayAwareInterface
{

    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this,$request);

        $model = $request->getModel();
        ArrayObject::ensureArrayObject($model);

        $order = $request->getFirstModel()->getOrder();
        $model['customer'] = $order->getCustomer();
        $model['locale'] = $order->getFallbackLocalCode($order->getLocaleCode());

        $borgunAction = $this->getBorgunAction($request->getToken(), $model);

        $this->getGateway()->execute($borgunAction);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }

    /**
     * @return \Payum\Core\GatewayInterface
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @param TokenInterface $token
     * @param ArrayObject $model
     *
     * @return SetBorgun
     */
    public function getBorgunAction(TokenInterface $token, ArrayObject $model)
    {
        $borgunAction = new SetBorgun($token);
        $borgunAction->setModel($model);

        return $borgunAction;
    }

    public function getFallbackLocaleCode($localCode)
    {
        return explode('_',$localCode)[0];
    }


    public function setGateway(GatewayInterface $gateway)
    {
        // TODO: Implement setGateway() method.
    }
}