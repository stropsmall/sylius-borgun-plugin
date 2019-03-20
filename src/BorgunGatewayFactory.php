<?php

/**
 * This file was created by the developers from Strops.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 */

namespace Strops\SyliusBorgunPlugin;

use BitBag\SyliusBorgunPlugin\Action\CaptureAction;
use BitBag\SyliusBorgunPlugin\Action\ConvertPaymentAction;
use BitBag\SyliusBorgunPlugin\Action\NotifyAction;
use BitBag\SyliusBorgunPlugin\Action\StatusAction;
use BitBag\SyliusBorgunPlugin\Bridge\OpenPayUBridge;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

/**
 * Class StropsSyliusBorgunPlugin
 * @package Strops\SyliusBorgunPlugin
 * @author Lubos Beran <l.beran@hotmail.sk>
 */
final class BorgunGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory.name' => 'borgun',
            'payum.factory_title' => 'Borgun',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction()
        ]);

        if(false === $config['payum.api']) {
            $config['payum.default_options'] = [
                'environment' => 'secure',
                'pos_id' => '',
                'signature_key' => ''
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['environment', 'pos_id', 'signature_key'];

            $config['payum.api'] = function(ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $borgunConfig = [
                    'environment' => $config['environment'],
                    'pos_id' => $config['pos_id'],
                    'signature_key' => $config['signature_key']
                ];

                return $borgunConfig;
            };

        }
    }
}