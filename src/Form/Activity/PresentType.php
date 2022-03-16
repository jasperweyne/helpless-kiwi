<?php

namespace App\Form\Activity;

use App\Entity\Activity\Registration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PresentType extends AbstractType
{
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
                    'label' => $registration->getPerson(),
                    'required' => true,
                    'expanded' => true,
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
