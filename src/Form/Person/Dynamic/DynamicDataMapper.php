<?php

namespace App\Form\Person\Dynamic;

use App\Entity\Person\Person;
use App\Entity\Person\PersonField;
use App\Entity\Person\PersonValue;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\DataTransformerInterface;

class DynamicDataMapper implements DataMapperInterface
{
    private $dynamicType = null;

    private $formField;

    private $person;

    public function __construct(Person $person)
    {
        $this->person = $person;
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
        $data = $this->getTransformer()->transform($valueObj->getValue());
        $forms[$this->formField]->setData($data);
    }

    public function mapFormsToData($forms, &$viewData)
    {
        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        if (is_null($viewData['value'])) {
            $field = $viewData['key'];

            $viewData['value'] = new PersonValue();
            $viewData['value']->setPerson($this->person);

            if ($field instanceof PersonField) {
                $viewData['value']->setField($field);
            } else {
                $viewData['value']->setBuiltin($field);
            }
        }

        if (!$viewData['value'] instanceof PersonValue) {
            throw new \UnexpectedValueException();
        }

        $raw = $forms[$this->formField]->getData();
        $data = $this->getTransformer()->reverseTransform($raw);

        // as data is passed by reference, overriding it will change it in
        // the form object as well
        // beware of type inconsistency, see caution below
        $viewData['value']->setValue($data);
    }

    public function setType(DynamicTypeInterface $dynamicType): self
    {
        $this->dynamicType = $dynamicType;

        return $this;
    }

    public function setFormField($formField): self
    {
        $this->formField = $formField;

        return $this;
    }

    private function getTransformer(): DataTransformerInterface
    {
        if (is_null($this->dynamicType)) {
            throw new \RuntimeException('setType must be called before getTransformer can be called');
        }

        return $this->dynamicType->getDataTransformer();
    }
}
