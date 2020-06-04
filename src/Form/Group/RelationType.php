<?php

namespace App\Form\Group;

use App\Entity\Group\Relation;
use App\Provider\Person\Person;
use App\Provider\Person\PersonRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RelationType extends AbstractType
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
