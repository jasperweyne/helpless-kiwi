<?php

namespace App\Form\Person;

use App\Entity\Person\PersonField;
use App\Form\Person\Dynamic\DynamicDataMapper;
use App\Form\Person\Dynamic\DynamicTypeRegistry;
use App\Form\Person\Dynamic\Type\StringType;
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
        $isCurrentUser = $options['current_user'];

        $transformer = new DynamicDataMapper($options['person'], $this->typeRegistry);

        $builder
            ->setDataMapper($transformer)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($transformer, $isCurrentUser) {
                if (null !== $keyVal = $event->getData()) {
                    $builder = $event->getForm();

                    $f = $keyVal['key'];

                    $field = null;

                    if ($f instanceof PersonField) {
                        if ($f->getUserEditOnly() && !$isCurrentUser) {
                            $builder->getParent()->remove($builder->getName());

                            return;
                        }

                        $field = $this->createFormField($f, $f->getName(), $f->getValueType());
                        $transformer->setType($f->getValueType());
                    } else {
                        $field = $this->createFormField($f);
                        $transformer->setType('string');
                    }

                    $transformer->setFormField(self::formRef($f));
                    $builder->add($field['name'], $field['type'], $field['options']);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefault('current_user', false)
            ->setDefault('label', false)
        ;
    }

    private function createFormField($key, ?string $name = null, ?string $valueType = null)
    {
        $type = new StringType();

        if (!is_null($valueType)) {
            $type = $this->typeRegistry->get($valueType);
        }

        // Build options
        $opts = $type->getDefaultOptions();

        if (!is_null($name)) {
            $opts['label'] = $name;
        }

        return [
            'name' => self::formRef($key),
            'type' => $type->getFormType(),
            'options' => $opts,
            'trans' => $type->getDataTransformer(),
        ];
    }
}
