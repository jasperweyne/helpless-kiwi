<?php

namespace App\Form\Activity;

use AppBundle\Entity\Tag;
use App\Provider\Person\PersonRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Activity\Registration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
            $registration = $event->getData();
            if (!$registration instanceof Registration) return;
            if ($registration->getDeleteDate() != null) return;

            $builder = $event->getForm();
            $builder->add('present', ChoiceType::class, [
                'choices' => [
                    'Onbekend' => null,
                    'Aanwezig' => True,
                    'Afwezig' => False,
                ],
                'label' => $this->personRegistry->find($registration->getPersonId()),
                'required'=> true,
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