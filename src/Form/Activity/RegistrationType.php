<?php

namespace App\Form\Activity;

use App\Entity\Activity\Registration;
use App\Provider\Person\PersonRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'choice_loader' => new CallbackChoiceLoader(function () {
                    $persons = [];
                    foreach ($this->personRegistry->findAll() as $person) {
                        $persons[$person->getCanonical()] = $person->getId();
                    }

                    return $persons;
                }),
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
            ->add('mail', CheckboxType::class, [
                'required' => false,
                'label' => 'Moet de persoon gemailt worden?',
                'mapped' => false,
                'data' => true,
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
