<?php

namespace App\Form\Activity;

use App\Form\Location\LocationType;
use App\Entity\Activity\Activity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('location', LocationType::class)
            ->add('deadline')
            ->add('author', EntityType::class, [
                'label' => 'Georganiseerd door',
                'class' => 'App\Entity\Group\Taxonomy',
                'choice_label' => function ($ref) {
                    return $ref->getName();
                },
            ])
            ->add('start')
            ->add('end')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
