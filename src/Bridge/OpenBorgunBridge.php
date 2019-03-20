<?php

namespace Strops\SyliusBorgunPlugin\Bridge;

final class OpenBorgunBridge implements OpenBorgunBridgeInterface
{
    /**
     * {@inheritDoc}
     */
    public function setAuthorizationDataApi($environment, $signatureKey, $posId)
    {
        \OpenBorgun_Configuration::setEnvironment($environment);
        \OpenBorgun_Configuration::setSignatureKey($signatureKey);
        \OpenBorgun_Configuration::setMerchantPosId($posId);
    }
    /**
     * {@inheritDoc}
     */
    public function create($order)
    {
        return \OpenBorgun_Order::create($order);
    }
    /**
     * {@inheritDoc}
     */
    public function retrieve($orderId)
    {
        return \OpenBorgun_Order::retrieve($orderId);
    }
    /**
     * {@inheritDoc}
     */
    public function consumeNotification($data)
    {
        return \OpenBorgun_Order::consumeNotification($data);
    }
}