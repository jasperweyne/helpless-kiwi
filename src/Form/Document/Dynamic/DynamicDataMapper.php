<?php

namespace App\Form\Document\Dynamic;

use App\Entity\Document\Document;
use App\Entity\Document\Field;
use App\Entity\Document\FieldValue;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\DataTransformerInterface;

class DynamicDataMapper implements DataMapperInterface
{
    private $dynamicType = null;

    private $formField;

    private $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
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
        if (is_null($valueObj) || !$valueObj instanceof FieldValue) {
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

            $viewData['value'] = new FieldValue();
            $viewData['value']->setDocument($this->document);

            if ($field instanceof Field) {
                $viewData['value']->setField($field);
            } else {
                $viewData['value']->setBuiltin($field);
            }
        }

        if (!$viewData['value'] instanceof FieldValue) {
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
