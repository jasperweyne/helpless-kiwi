<?php

namespace App\Form\Activity;

use App\Entity\Activity\Registration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, [
                'attr' => ['data-select' => 'true'],
                'label' => 'Naam',
                'class' => 'App\Entity\Person\Person',
                'choice_label' => function ($ref) {
                    return $ref;
                },
                'required' => true,
            ])
            ->add('option', EntityType::class, [
                'label' => 'Optie',
                'class' => 'App\Entity\Activity\PriceOption',
                'choices' => $options['allowed_options'],
                'choice_label' => function ($ref) {
                    return $ref;
                },
                'required' => true,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Registration::class,
            'allowed_options' => [],
        ]);
    }
}
