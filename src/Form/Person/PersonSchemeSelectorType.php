<?php

namespace App\Form\Person;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\Document\DocumentSchemeSelectorType;

class PersonSchemeSelectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('document', DocumentSchemeSelectorType::class, ['schemes'=>$options['schemes']]);

        ;
    }

    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('schemes')   
        ;
    }
}
