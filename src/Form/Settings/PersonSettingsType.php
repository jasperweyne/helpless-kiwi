<?php

namespace App\Form\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('person_name_expr_default', TextType::class, [
                'required' => false,
                'label' => 'Default Name Expression',
            ])
            ->add('person_shortname_expr_default', TextType::class, [
                'required' => false,
                'label' => 'Default Shortname Expression',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
