<?php

namespace App\Form\Activity;

use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ActivityNewType extends ActivityEditType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image file',
                'required' => true,
                'allow_delete' => false,
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prijs',
                'divisor' => 100,
                'mapped' => false,
                'required' => false,
                'help' => 'Indien er geen/meerdere aanmeldmogelijkheden zijn, laat dit veld leeg',
                'attr' => [
                    'placeholder' => '0,00',
                ],
            ]);
    }
}
