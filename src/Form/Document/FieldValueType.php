<?php

namespace App\Form\Document;

use App\Entity\Document\Field;
use App\Form\Document\Dynamic\DynamicDataMapper;
use App\Form\Document\Dynamic\DynamicTypeRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FieldValueType extends AbstractType
{
    private $typeRegistry;

    public function __construct(DynamicTypeRegistry $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mapper = new DynamicDataMapper($options['document']);

        $builder
            ->setDataMapper($mapper)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($mapper) {
                if (null !== $keyVal = $event->getData()) {
                    $builder = $event->getForm();
                    $field = $keyVal['key'];

                    // Set get DynamicTypeInterface instance
                    $valueType = $field instanceof Field ? $field->getValueType() : 'string';
                    $type = $this->typeRegistry->get($valueType);

                    // Set id of new form
                    $formId = $field;
                    if ($field instanceof Field) {
                        $formId = $field->getId();
                    }

                    // Update the mapper
                    $mapper
                        ->setFormField($formId)
                        ->setType($type)
                    ;

                    // Build options
                    $opts = $type->getDefaultOptions();

                    if ($field instanceof Field) {
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
            ->setRequired('document')
            ->setDefault('label', false)
        ;
    }
}
