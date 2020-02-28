<?php

namespace App\Form\Person;

use App\Form\Document\DocumentType;
use App\Entity\Person\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Person entity fields, not in the dynamic schema.
        $builder->add('email', EmailType::class);

        //The document subform. It covers all dynamic fields, note that the options must be parsed.
        //As person is not required or defaulted in DocumentType, you can't just parse the options. 
        $builder->add('document', DocumentType::class, ['document'=>$options['document'],'current_user'=>$options['current_user']]);
    
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('person')
            ->setRequired('document')
            ->setDefault('current_user', false)
            ->setDefault('data_class', Person::class)
        ;
    }

}
