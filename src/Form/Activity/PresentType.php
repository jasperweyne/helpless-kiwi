<?php

namespace App\Form\Activity;

use App\Entity\Activity\Registration;
use App\Provider\Person\PersonRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PresentType extends AbstractType
{
    protected $personRegistry;

    public function __construct(PersonRegistry $personRegistry)
    {
        $this->personRegistry = $personRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $builder = $event->getForm();
            $registration = $event->getData();
            if (!$registration instanceof Registration) {
                return;
            }
            if (null != $registration->getDeleteDate()) {
                return;
            }

            $builder
                ->add('present', ChoiceType::class, [
                    'choices' => [
                        'Onbekend' => null,
                        'Aanwezig' => true,
                        'Afwezig' => false,
                    ],
                    'label' => $this->personRegistry->find($registration->getPersonId()),
                    'required' => true,
                ])
                ->add('comment', TextType::class, [
                    'required' => false,
                ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Registration::class,
            'label' => false,
        ]);
    }
}
