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
    public static function formRef($key)
    {
        if ($key instanceof PersonField) {
            return $key->getId();
        } elseif (is_string($key)) {
            return $key;
        }
        throw new \LogicException('Type: '.gettype($key));
    }

    private $typeRegistry;

    public function __construct(DynamicTypeRegistry $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new DynamicDataMapper($options['person']);

        $builder
            ->setDataMapper($transformer)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($transformer) {
                if (null !== $keyVal = $event->getData()) {
                    $builder = $event->getForm();

                    $field = $keyVal['key'];

                    $valueType = $field instanceof PersonField ? $field->getValueType() : 'string';

                    $formId = self::formRef($field);
                    $type = $this->typeRegistry->get($valueType);

                    $transformer
                        ->setFormField($formId)
                        ->setType($type)
                    ;

                    // Build options
                    $opts = $type->getDefaultOptions();

                    if ($field instanceof PersonField) {
                        $opts['label'] = $field->getName();
                    }

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
