<?php

/**
 * This file was created by the developers from Strops.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 */

namespace Strops\SyliusBorgunPlugin\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Strops\SyliusBorgunPlugin\Bridge\OpenBorgunBridge;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Convert;

/**
 * Class ConvertPaymentAction
 * @package Strops\SyliusBorgunPlugin\Action
 * @author  Lubos Beran <l.beran@hotmail.sk>
 */
final class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /**
         * @var $payment PaymentInterface
         */
        $payment = $request->getSource();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['totalAmount'] = $payment->getTotalAmount();
        $details['currencyCode'] = $payment->getCurrencyCode();
        $details['extOrderId'] = uniqid($payment->getNumber());
        $details['description'] = $payment->getDescription();
        $details['client_email'] = $payment->getClientEmail();
        $details['client_id'] = $payment->getClientId();
        $details['customerIp'] = $this->getClientIp();
        $details['status']  = OpenBprgunBridge::NEW_API_STATUS;
        // TODO: Implement execute() method.
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getTo() === 'array'
            ;
    }

    /**
     * @return string|null
     */
    private function getClientIp()
    {
        return array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : null;
    }
}