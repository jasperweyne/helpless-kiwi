<?php

namespace App\Form\Person;


use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\Person\Person;
use App\Form\Document\DocumentType;


class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $person = $builder->getData();
        $builder
            ->add('email', EmailType::class)
            //The document subform. It covers all dynamic fields, note that the options must be parsed.
            //As person is not required or defaulted in DocumentType, you can't just parse the options. 
            ->add('document', DocumentType::class, ['document'=>$person->getDocument(),'current_user'=>$options['current_user']]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('current_user', false)
            ->setDefault('data_class', Person::class)
        ;
    }
}
