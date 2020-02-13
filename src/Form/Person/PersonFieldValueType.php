<?php

namespace App\Form\Person;

use App\Entity\Person\PersonField;
use App\Entity\Person\PersonValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PersonFieldValueType extends AbstractType
{
    public static function TYPES()
    {
        return [
            'string' => [
                'type' => TextType::class,
                'defs' => [],
            ],
            'switch' => [
                'type' => CheckboxType::class,
                'defs' => [
                    'required' => false,
                ],
                'trans' => new CallbackTransformer(
                    function ($encodedToBool) {
                        // transform the encoded value back to a boolean
                        return json_decode($encodedToBool);
                    },
                    function ($switchToString) {
                        // transform the form value to a string
                        return json_encode($switchToString);
                    }
                ),
            ],
        ];
    }

    public static function formRef($key)
    {
        if ($key instanceof PersonField) {
            return $key->getId();
        } elseif (is_string($key)) {
            return $key;
        }
        throw new \LogicException('Type: '.gettype($key));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isCurrentUser = $options['current_user'];

        $transformer = new DynamicDataMapper();

        $builder
            ->setDataMapper($transformer)
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($transformer, $isCurrentUser) {
                if (null !== $keyVal = $event->getData()) {
                    $builder = $event->getForm();

                    $f = $keyVal['key'];
                    $v = $keyVal['value'];

                    if ($v instanceof PersonValue) {
                        $v = $v->getValue();
                    }

                    $field = null;

                    if ($f instanceof PersonField) {
                        if ($f->getUserEditOnly() && !$isCurrentUser) {
                            return;
                        }

                        $field = $this->createFormField($f, $v, $f->getName(), $f->getValueType());
                        $transformer->setType($f->getValueType());
                    } else {
                        $field = $this->createFormField($f, $v);
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
            ->setDefault('current_user', false)
            ->setDefault('label', false)
        ;
    }

    private function createFormField($key, $value, ?string $name = null, ?string $valueType = null)
    {
        $type = TextType::class;
        $opts = [];
        $trans = null;

        if (!is_null($valueType)) {
            if (array_key_exists($valueType, self::TYPES())) {
                $type = self::TYPES()[$valueType]['type'] ?? TextType::class;
                $opts = self::TYPES()[$valueType]['defs'] ?? [];
                $trans = self::TYPES()[$valueType]['trans'] ?? null;
            } else {
                throw new \UnexpectedValueException("Unknown person field type '".$valueType."'");
            }
        }

        if (!is_null($name)) {
            $opts['label'] = $name;
        }

        $opts['mapped'] = false;

        return [
            'name' => self::formRef($key),
            'type' => $type,
            'options' => $opts,
            'trans' => $trans,
            'value' => $value,
        ];
    }
}
