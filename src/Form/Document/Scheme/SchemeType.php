<?php

namespace App\Form\Document\Scheme;

use App\Entity\Document\Scheme\Scheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SchemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('schemeDefault', EntityType::class, [
                'label' => 'Default schema type',
                'class' => 'App\Entity\Document\Scheme\SchemeDefault',
                'choices'=> $options['defaults'],
                'choice_label' => function ($ref) {
                    return $ref->getName();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
        ->setDefaults([
            'data_class' => Scheme::class,
        ])
        ->setRequired('defaults');
    }
}
