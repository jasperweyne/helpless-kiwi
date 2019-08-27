<?php

namespace App\Form\Activity;

use App\Form\Location\LocationType;
use App\Entity\Activity\Activity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description', TextareaType::class)
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
            ->add('imageFile', VichImageType::class, [
                'required' => true,
                'allow_delete' => false,
            ])
            ->add('color', ChoiceType::class, [
                'choices'  => [
                    '' => null,
                    'Rood' => 'red',
                    'Oranje' => 'orange',
                    'Geel' => 'yellow',
                    'Groen' => 'green',
                    'Cyaan' => 'cyan',
                    'Lichtblauw' => 'ltblue',
                    'Blauw' => 'blue',
                    'Paars' => 'purple',
                    'Roze' => 'pink',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
