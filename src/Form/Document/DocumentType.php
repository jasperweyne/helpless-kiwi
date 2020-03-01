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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('keyValues', CollectionType::class, [
            'entry_type' => FieldValueType::class,
            'entry_options' => [
                'document' => $options['document']
            ],
            'label' => false,
        ])
        ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            if (null !== $event->getData()) {
                $builder = $event->getForm();
                $keyValues = $builder->get('keyValues');

                foreach ($keyValues->all() as $formField) {
                    $field = $formField->getData();

                    if (!$field['key'] instanceof Field) {
                        continue;
                    }

                    if ($field['key']->getUserEditOnly() && !$options['current_user']) {
                        $keyValues->remove($formField->getName());
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('document')
            ->setDefault('current_user', false)
            ->setDefault('data_class', Document::class)
        ;
    }


}
