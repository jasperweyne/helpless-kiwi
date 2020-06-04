<?php

namespace App\Form\Activity;

use App\Entity\Activity\Registration;
use App\Provider\Person\Person;
use App\Provider\Person\PersonRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationType extends AbstractType
{
    private $personRegistry;

    public function __construct(PersonRegistry $personRegistry)
    {
        $this->personRegistry = $personRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person_id', ChoiceType::class, [
                'attr' => ['data-select' => 'true'],
                'label' => 'Naam',
                'choices' => $this->personRegistry->findAll(),
                'choice_value' => 'id',
                'choice_label' => function(?Person $person) {
                    return $person->getCanonical();
                },
                'required' => true,
            ])
            ->add('option', EntityType::class, [
                'label' => 'Optie',
                'class' => 'App\Entity\Activity\PriceOption',
                'choices' => $options['allowed_options'],
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
            'data_class' => Registration::class,
            'allowed_options' => [],
        ]);
    }
}
