<?php

namespace App\Form\Person;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PersonSchemeSelectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('scheme', EntityType::class, [
                'label' => 'Persoon schema',
                'class' => 'App\Entity\Person\PersonScheme',
                'choice_label' => function ($ref) {
                    return $ref->getName();
                },
            ])
        ;
    }
}
