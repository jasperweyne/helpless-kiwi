<?php

namespace App\Form\Person;

use App\Entity\Person\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefined('name')
            ->setDefined('type')
            ->setDefault('options', [])
        ;

        // Name fields
        foreach ($options['nameFields'] as $option) {
            $field = $resolver->resolve($option);
            $builder->add($field['name'], $field['type'], $field['options']);
        }

        // Person entity fields
        $builder
            ->add('email', EmailType::class)
        ;

        // Other fields
        foreach ($options['fields'] as $option) {
            $field = $resolver->resolve($option);
            $builder->add($field['name'], $field['type'], $field['options']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'fields' => [],
            'nameFields' => [],
        ]);
    }
}
