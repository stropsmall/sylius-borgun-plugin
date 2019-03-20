<?php

namespace Strops\SyliusBorgunPlugin\Action;
use Strops\SyliusBorgunPlugin\Bridge\OpenBorgunBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
/**
 * @author Lubos Beran <l.beran@hotmail.sk>
 */
final class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request GetStatusInterface */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $status = isset($model['statusBorgun']) ? $model['statusBorgun'] : null;
        if ((null === $status || OpenBorgunBridgeInterface::NEW_API_STATUS === $status) && false === isset($model['orderId'])) {
            $request->markNew();
            return;
        }
        if (OpenBorgunBridgeInterface::PENDING_API_STATUS === $status) {
            return;
        }
        if (OpenBorgunBridgeInterface::CANCELED_API_STATUS === $status) {
            $request->markCanceled();
            return;
        }
        if (OpenBorgunBridgeInterface::WAITING_FOR_CONFIRMATION_PAYMENT_STATUS === $status) {
            $request->markSuspended();
            return;
        }
        if (OpenBorgunBridgeInterface::COMPLETED_API_STATUS === $status) {
            $request->markCaptured();
            return;
        }
        $request->markUnknown();
    }
    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}