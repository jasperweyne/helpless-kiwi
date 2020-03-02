<?php

namespace App\Form\Document;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Document\Document;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;

class DocumentSchemeSelectorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('scheme', EntityType::class, [
                'label' => 'Persoon schema',
                'class' => 'App\Entity\Document\Scheme\Scheme',
                'choices'=> $options['schemes'],
                'choice_label' => function ($ref) {
                    return $ref->getName();
                },
            ])
        ;
    }

    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('schemes') 
            ->setDefault('data_class', Document::class)  
        ;
    }
}
