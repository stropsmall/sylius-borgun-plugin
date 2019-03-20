<?php

namespace Strops\SyliusBorgunPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
/**
 * @author Lubos Beran <l.beran@hotmail.sk>
 */
final class BorgunGatewayConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('environment', ChoiceType::class, [
                'choices' => [
                    'strops.borgun_plugin.secure' => 'secure',
                    'strops.borgun_plugin.sandbox' => 'sandbox',
                ],
                'label' => 'strops.borgun_plugin.environment',
            ])
            ->add('signature_key', TextType::class, [
                'label' => 'strops.borgun_plugin.signature_key',
                'constraints' => [
                    new NotBlank([
                        'message' => 'strops.borgun_plugin.gateway_configuration.signature_key.not_blank',
                        'groups' => ['sylius'],
                    ])
                ],
            ])
            ->add('pos_id', TextType::class, [
                'label' => 'strops.borgun_plugin.pos_id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'strops.borgun_plugin.gateway_configuration.pos_id.not_blank',
                        'groups' => ['sylius'],
                    ])
                ],
            ])
        ;
    }
}