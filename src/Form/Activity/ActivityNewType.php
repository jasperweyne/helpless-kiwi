<?php

namespace App\Form\Activity;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ActivityNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $activityBuilder = $builder->create('activity', ActivityEditType::class, ['label' => false]);

        $activityBuilder
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image file',
                'required' => true,
                'allow_delete' => false,
            ]);

        $builder
            ->add($activityBuilder)
            ->add('price', MoneyType::class, [
                'label' => 'Prijs',
                'divisor' => 100,
                'required' => false,
                'help' => 'Indien er geen/meerdere aanmeldmogelijkheden zijn, laat dit veld leeg',
                'attr' => [
                    'placeholder' => '0,00',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ActivityCreationData::class,
        ]);
    }
}
