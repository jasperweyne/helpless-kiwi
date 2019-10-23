<?php

namespace App\Form\Activity;

use App\Entity\Activity\Registration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person', EntityType::class, [
                'label' => 'Naam',
                'class' => 'App\Entity\Person\Person',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.firstname', 'ASC');
                },
                'choice_label' => function ($ref) {
                    return $ref->getFirstname().' '.$ref->getLastname();
                },
            ])
            ->add('option', EntityType::class, [
                'label' => 'Optie',
                'class' => 'App\Entity\Activity\PriceOption',
                'choices' => $options['allowed_options'],
                'choice_label' => function ($ref) {
                    return $ref->getName().' '.number_format($ref->getPrice() / 100, 2, '.', '').'€';
                },
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Registration::class,
            'allowed_options' => [],
        ]);
    }
}
