<?php

namespace App\Form\Security;

use App\Entity\Security\LocalAccount;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;

class GenerateTokenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('account', EntityType::class, [
                'class' => LocalAccount::class,
            ])
            ->add('expiresAt', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'label' => 'Token verloopt op',
                'data' => new \DateTime('+5 minutes'),
            ])
        ;
    }
}
