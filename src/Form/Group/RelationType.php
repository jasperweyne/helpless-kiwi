<?php

namespace App\Form\Group;

use App\Entity\Group\Relation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class RelationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, [
                'attr' => ['data-select' => 'true'],
                'label' => 'Naam',
                'class' => 'App\Entity\Person\Person',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.firstname', 'ASC');
                },
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
            'data_class' => Relation::class,
            'allowed_options' => [],
        ]);
    }
}
