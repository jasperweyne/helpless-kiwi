<?php

namespace App\Form\Person;

use App\Entity\Person\PersonField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('keyValues', CollectionType::class, [
                'entry_type' => PersonFieldValueType::class,
                'entry_options' => [
                    'person' => $builder->getData(),
                ],
                'label' => false,
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
                if (null !== $event->getData()) {
                    $builder = $event->getForm();
                    $keyValues = $builder->get('keyValues');

                    foreach ($keyValues->all() as $formField) {
                        $field = $formField->getData();

                        if (!$field['key'] instanceof PersonField) {
                            continue;
                        }

                        if ($field['key']->getUserEditOnly() && !$options['current_user']) {
                            $keyValues->remove($formField->getName());
                        }
                    }
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('current_user', false)
        ;
    }
}
