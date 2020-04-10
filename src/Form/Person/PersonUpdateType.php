<?php

namespace App\Form\Person;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PersonUpdateType extends PersonType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('email')
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                if (null !== $event->getData()) {
                    $builder = $event->getForm();
                    $keyValues = $builder->get('keyValues');

                    foreach ($keyValues->all() as $formField) {
                        $field = $formField->getData();

                        if (!is_null($field['value'])) {
                            $keyValues->remove($formField->getName());
                        }
                    }
                }
            })
        ;
    }
}
