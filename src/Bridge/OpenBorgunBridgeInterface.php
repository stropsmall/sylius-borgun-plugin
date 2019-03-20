<?php

namespace Strops\SyliusBorgunPlugin\Bridge;


interface OpenBorgunBridgeInterface
{
    const NEW_API_STATUS = 'NEW';
    const PENDING_API_STATUS = 'PENDING';
    const COMPLETED_API_STATUS = 'COMPLETED';
    const SUCCESS_API_STATUS = 'SUCCESS';
    const CANCELED_API_STATUS = 'CANCELED';
    const COMPLETED_PAYMENT_STATUS = 'COMPLETED';
    const PENDING_PAYMENT_STATUS = 'PENDING';
    const CANCELED_PAYMENT_STATUS = 'CANCELED';
    const WAITING_FOR_CONFIRMATION_PAYMENT_STATUS = 'WAITING_FOR_CONFIRMATION';
    const REJECTED_STATUS = 'REJECTED';
    /**
     * @param $environment
     * @param $signatureKey
     * @param $posId
     */
    public function setAuthorizationDataApi($environment, $signatureKey, $posId);
    /**
     * @param $order
     */
    public function create($order);
    /**
     * @param string $orderId
     *
     * @return object
     */
    public function retrieve($orderId);
    /**
     * @param $data
     * @return null|\OpenPayU_Result
     *
     * @throws \OpenPayU_Exception
     */
    public function consumeNotification($data);
}
