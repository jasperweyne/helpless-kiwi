<?php

namespace App\Form\Document;

use App\Entity\Person\Person;
use Symfony\Component\Form\FormBuilderInterface;

class DocumentUpdateType extends DocumentType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('email');

        $fields = $this->buildRemoveFields($options['person']);

        // Other fields
        foreach ($fields as $field) {
            $builder->remove($field);
        }
    }

    private function buildRemoveFields(Person $person)
    {
        $fields = [];
        foreach ($person->getKeyValues() as $keyVal) {
            if (!is_null($keyVal['value'])) {
                $fields[] = self::formRef($keyVal['key']);
            }
        }

        return $fields;
    }
}
