<?php

namespace App\Form\Activity;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Naam',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschrijving',
            ])
            ->add('author', EntityType::class, [
                'label' => 'Georganiseerd door',
                'class' => 'App\Entity\Reference',
                'choice_label' => function ($ref) {
                    return $ref->getName();
                },

            ])
            ->add('start', DateTimeType::class, [
                'label' => 'Begint op',
            ])
            ->add('end', DateTimeType::class, [
                'label' => 'Eindigt op',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Activity\Activity',
        ]);
    }
}
