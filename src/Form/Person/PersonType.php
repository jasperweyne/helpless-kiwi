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
        // Person entity fields
        $builder
            ->add('email', EmailType::class)
        ;

        $resolver = new OptionsResolver();
        $resolver
            ->setDefined('name')
            ->setDefined('type')
            ->setDefault('options', [])
        ;

        // Other fields
        foreach ($options['fields'] as $option) {
            $field = $resolver->resolve($option);
            $field['options']['mapped'] = false;
            $builder->add($field['name'], $field['type'], $field['options']);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('fields', []);
    }
}
