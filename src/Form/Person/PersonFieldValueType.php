<?php

namespace App\Form\Person;

use App\Entity\Person\PersonField;
use App\Form\Person\Dynamic\DynamicDataMapper;
use App\Form\Person\Dynamic\DynamicTypeRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PersonFieldValueType extends AbstractType
{
    private $typeRegistry;

    public function __construct(DynamicTypeRegistry $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mapper = new DynamicDataMapper($options['person']);

        $builder
            ->setDataMapper($mapper)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($mapper) {
                if (null !== $keyVal = $event->getData()) {
                    $builder = $event->getForm();
                    $field = $keyVal['key'];

                    // Set get DynamicTypeInterface instance
                    $valueType = $field instanceof PersonField ? $field->getValueType() : 'string';
                    $type = $this->typeRegistry->get($valueType);

                    // Set id of new form
                    $formId = $field;
                    if ($field instanceof PersonField) {
                        $formId = $field->getId();
                    }

                    // Update the mapper
                    $mapper
                        ->setFormField($formId)
                        ->setType($type)
                    ;

                    // Build options
                    $opts = $type->getDefaultOptions();

                    if ($field instanceof PersonField) {
                        $opts['label'] = $field->getName();
                    }

                    // Add form
                    $builder->add($formId, $type->getFormType(), $opts);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefault('label', false)
        ;
    }
}
