<?php

namespace App\Form\Person;

<<<<<<< HEAD
use App\Form\Document\DocumentType;
use App\Entity\Person\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
=======
use App\Entity\Person\PersonField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
>>>>>>> develop

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
<<<<<<< HEAD
        // Person entity fields, not in the dynamic schema.
        $builder->add('email', EmailType::class);

        //The document subform. It covers all dynamic fields, note that the options must be parsed.
        //As person is not required or defaulted in DocumentType, you can't just parse the options. 
        $builder->add('document', DocumentType::class, ['document'=>$options['document'],'current_user'=>$options['current_user']]);
    
=======
        $builder
            ->add('email', EmailType::class)
            ->add('keyValues', CollectionType::class, [
                'entry_type' => PersonFieldValueType::class,
                'entry_options' => [
                    'person' => $builder->getData(),
                ],
                'label' => false,
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
                if (null !== $event->getData()) {
                    $builder = $event->getForm();
                    $keyValues = $builder->get('keyValues');

                    foreach ($keyValues->all() as $formField) {
                        $field = $formField->getData();

                        if (!$field['key'] instanceof PersonField) {
                            continue;
                        }

                        if ($field['key']->getUserEditOnly() && !$options['current_user']) {
                            $keyValues->remove($formField->getName());
                        }
                    }
                }
            })
        ;
>>>>>>> develop
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
<<<<<<< HEAD
            ->setRequired('person')
            ->setRequired('document')
=======
>>>>>>> develop
            ->setDefault('current_user', false)
            ->setDefault('data_class', Person::class)
        ;
    }
<<<<<<< HEAD

=======
>>>>>>> develop
}
