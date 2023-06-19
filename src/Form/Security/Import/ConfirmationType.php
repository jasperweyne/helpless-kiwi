<?php

namespace App\Form\Security\Import;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfirmationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('willAdd', CheckboxType::class, [
                'label' => 'Nieuwe accounts toevoegen',
                'required' => false,
            ])
            ->add('willRemove', CheckboxType::class, [
                'label' => 'Afwezige accounts verwijderen',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ImportedAccounts::class,
        ]);
    }
}
