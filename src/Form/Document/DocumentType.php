<?php

namespace App\Form\Document;

use App\Entity\Document\AccesGroup;
use App\Entity\Document\Document;
use App\Entity\Person\Person;
use App\Entity\Document\Field;
use App\Entity\Document\FieldValue;
use App\Entity\Document\Expression;
use App\Entity\Document\ExpressionValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DocumentType extends AbstractType
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
        if ($key instanceof Field || $key instanceof Expression) {
            return $key->getId();
        } elseif (is_string($key)) {
            return $key;
        }
        throw new \LogicException();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Person entity fields
        

        $fields = $this->buildFormFields($options['document'], $options['current_user']);

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
            ->setRequired('document')
            ->setDefault('current_user', false)
        ;
    }

    private function buildFormFields(Document $document, Person $user)
    {
        $fields = [];
        
        foreach ($document->getKeyValues() as $keyVal) {
            $f = $keyVal['key'];
            $v = $keyVal['value'];

            if ($v instanceof FieldValue) {
                $v = $v->getValue();
            }
            if ($v instanceof ExpressionValue) {
                $v = $v->getExpression()->getExpression();
            }

            if ($f instanceof Field) {
                //Acces group coding
                //It checks if the edit acces group is contained  
                //in the document specific acces group collection of the user.
                if ($user->userAccesGroups($document)->contains($f->getCanEdit()) || $f->getCanEdit()==null) {
                    $fields[] = $this->createEditFormField($f, $v, $f->getName(), $f->getValueType());
                } elseif ($user->userAccesGroups($document)->contains($f->getCanView()) || $f->getCanView()==null) {
                    $fields[] = $this->createViewFormField($f, $v, $f->getName(), $f->getValueType());
                }
            } elseif ($f instanceof Expression)  {
                if ($user->userAccesGroups($document)->contains($f->getCanEdit()) || $f->getCanEdit()==null) {
                    $fields[] = $this->createViewFormField($f, $v, $f->getName());
                } elseif ($user->userAccesGroups($document)->contains($f->getCanView()) || $f->getCanView()==null) {
                    $fields[] = $this->createViewFormField($f, $v, $f->getName());
                }
            } else {
                $fields[] = $this->createFormField($f, $v);
            }
        }
        return $fields;
    }

    private function createEditFormField($key, $value, ?string $name = null, ?string $valueType = null)
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

    private function createViewFormField($key, $value, ?string $name = null)
    {
        $type = TextType::class;
        $opts = [];
        $trans = null;

        if (!is_null($name)) {
            $opts['label'] = $name;
        }

        $opts['mapped'] = false;
        $opts['attr'] = ['readonly' => true];
        return [
            'name' => self::formRef($key),
            'type' => $type,
            'options' => $opts,
            'trans' => $trans,
            'value' => $value,
        ];
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
