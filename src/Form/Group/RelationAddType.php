<?php

namespace App\Form\Group;

use App\Entity\Group\Relation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class RelationAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', EntityType::class, [
                'attr' => ['data-select' => 'true'],
                'label' => 'Naam',
                'class' => 'App\Entity\Group\Group',
                'choice_label' => function ($ref) {
                    return $ref->getName();
                },
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Relation::class,
            'allowed_options' => [],
        ]);
    }
}
