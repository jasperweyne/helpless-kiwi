<?php

namespace App\Form\Group;

use App\Entity\Group\Relation;
use App\Provider\Person\Person;
use App\Provider\Person\PersonRegistry;
use Symfony\Component\Form\AbstractType;
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
            ->add('person', ChoiceType::class, [
                'attr' => ['data-select' => 'true'],
                'label' => 'Naam',
                'choices' => $this->personRegistry->findAll(),
                'choice_value' => 'id',
                'choice_label' => function($ref) {
                    return $ref->getCanonical();
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
