<?php

namespace App\Form\Person;

use App\Entity\Person\PersonScheme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonSchemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('name_expr', TextType::class, [
                'required' => false,
                'label' => 'Name Expression',
            ])
            ->add('shortname_expr', TextType::class, [
                'required' => false,
                'label' => 'Shortname Expression',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PersonScheme::class,
        ]);
    }
}
