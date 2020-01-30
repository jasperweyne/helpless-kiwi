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

    public static function fieldRef(PersonField $personField)
    {
        return $personField->getId();
    }

    public static function valueRef(PersonValue $personValue)
    {
        if (!$personValue->getBuiltin()) {
            return self::fieldRef($personValue->getField());
        }

        return $personValue->getBuiltin();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Person entity fields
        $builder
            ->add('email', EmailType::class)
        ;

        $fields = $this->buildFormFields($options['person']);

        // Other fields
        foreach ($fields as $option) {
            $builder->add($option['name'], $option['type'], $option['options']);

            if (!is_null($option['trans'])) {
                $builder->get($option['name'])->addModelTransformer($option['trans']);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('person');
    }

    private function buildFormFields(Person $person)
    {
        $fields = [];
        if ($person->getScheme()) {
            $schemeFields = $person->getScheme()->getFields();

            foreach ($schemeFields as $field) {
                $fields[] = $this->parsePersonField($field);
            }
        } else {
            $personValues = $person->getFieldValues();

            foreach ($personValues as $value) {
                $fields[] = $this->parsePersonValue($value);
            }
        }

        return $fields;
    }

    private function parsePersonField(PersonField $f)
    {
        return $this->createFormField(self::fieldRef($f), $f->getName(), $f->getValueType());
    }

    private function parsePersonValue(PersonValue $v)
    {
        if (!$v->getBuiltin()) {
            return $this->parsePersonField($v->getField());
        }

        return $this->createFormField(self::valueRef($v));
    }

    private function createFormField(string $id, ?string $name = null, ?string $valueType = null)
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
            'name' => $id,
            'type' => $type,
            'options' => $opts,
            'trans' => $trans,
        ];
    }
}
