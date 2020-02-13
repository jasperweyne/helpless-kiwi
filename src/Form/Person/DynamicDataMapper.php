<?php

namespace App\Form\Person;

use App\Entity\Person\PersonValue;
use LogicException;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DynamicDataMapper implements DataMapperInterface, DataTransformerInterface
{
    private $transformer = null;

    private $formField;

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

    public function mapDataToForms($viewData, $forms)
    {
        // there is no data yet, so nothing to prepopulate
        if (null === $viewData) {
            return;
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $valueObj = $viewData['value'];
        if (is_null($valueObj) || !$valueObj instanceof PersonValue) {
            return;
        }

        // initialize form field values
        $data = $this->transform($valueObj->getValue());
        $forms[$this->formField]->setData($data);
    }

    public function mapFormsToData($forms, &$viewData)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        $valueObj = $viewData['value'];
        if (is_null($valueObj) || !$valueObj instanceof PersonValue) {
            throw new LogicException();
        }

        $raw = $forms[$this->formField]->getData();
        $data = $this->reverseTransform($raw);

        // as data is passed by reference, overriding it will change it in
        // the form object as well
        // beware of type inconsistency, see caution below
        $viewData['value']->setValue($data);
    }

    public function setType(string $type): self
    {
        $this->transformer = $type;

        return $this;
    }

    public function setFormField($formField): self
    {
        $this->formField = $formField;

        return $this;
    }

    public function transform($value)
    {
        return $this->getTransformer()->transform($value);
    }

    public function reverseTransform($value)
    {
        return $this->getTransformer()->reverseTransform($value);
    }

    private function getTransformer(): DataTransformerInterface
    {
        if (is_null($this->transformer) || !array_key_exists($this->transformer, self::TYPES())) {
            throw new TransformationFailedException();
        }

        $identityFn = function ($x) { return $x; };

        $type = self::TYPES()[$this->transformer];
        if (!array_key_exists('trans', $type)) {
            return new CallbackTransformer($identityFn, $identityFn);
        }

        $trans = $type['trans'];
        if (!$trans instanceof DataTransformerInterface) {
            throw new TransformationFailedException();
        }

        return $trans;
    }
}
