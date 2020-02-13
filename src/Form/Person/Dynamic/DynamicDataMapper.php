<?php

namespace App\Form\Person\Dynamic;

use App\Entity\Person\PersonValue;
use LogicException;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DynamicDataMapper implements DataMapperInterface, DataTransformerInterface
{
    private $transformer = null;

    private $formField;

    private $typeRegistry;

    public function __construct(DynamicTypeRegistry $typeRegistry)
    {
        $this->typeRegistry = $typeRegistry;
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
        if (is_null($this->transformer) || !$this->typeRegistry->has($this->transformer)) {
            throw new TransformationFailedException();
        }
        
        return $this->typeRegistry->get($this->transformer)->getDataTransformer();
    }
}
