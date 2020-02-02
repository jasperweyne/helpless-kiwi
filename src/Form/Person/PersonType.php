<?php

namespace App\Form\Person;

use App\Entity\Person\Person;
use App\Entity\Person\PersonField;
use App\Entity\Person\PersonValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PersonType extends AbstractType
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
        throw new \LogicException();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Person entity fields
        $builder
            ->add('email', EmailType::class)
        ;

        $fields = $this->buildFormFields($options['person'], $options['current_user']);

        // Other fields
        foreach ($fields as $option) {
            $builder->add($option['name'], $option['type'], $option['options']);

            if (!is_null($option['value'])) {
                $builder->get($option['name'])
                    ->setData($option['value'])
                ;
            }

            if (!is_null($option['trans'])) {
                $builder->get($option['name'])
                    ->addModelTransformer($option['trans'])
                ;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setDefault('current_user', false)
        ;
    }

    private function buildFormFields(Person $person, bool $isCurrentUser)
    {
        $fields = [];
        foreach ($person->getKeyValues() as $keyVal) {
            $f = $keyVal['key'];
            $v = $keyVal['value'];

            if ($v instanceof PersonValue) {
                $v = $v->getValue();
            }

            if ($f instanceof PersonField) {
                if ($f->getUserEditOnly() && !$isCurrentUser) {
                    continue;
                }

                $fields[] = $this->createFormField($f, $v, $f->getName(), $f->getValueType());
            } else {
                $fields[] = $this->createFormField($f, $v);
            }
        }

        return $fields;
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
