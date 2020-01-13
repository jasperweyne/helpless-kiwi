<?php

namespace App\Form\Activity;

use App\Form\Location\LocationType;
use App\Entity\Activity\Activity;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ActivityEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description', TextareaType::class)
            ->add('location', LocationType::class)
            ->add('deadline', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('author', EntityType::class, [
                'label' => 'Georganiseerd door',
                'class' => 'App\Entity\Group\Group',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->andWhere('t.active = TRUE');
                },
                'choice_label' => function ($ref) {
                    return $ref->getName();
                },
            ])
            ->add('start', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('end', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('color', ChoiceType::class, [
                'attr' => ['data-select' => 'true'],
                'choices' => [
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
