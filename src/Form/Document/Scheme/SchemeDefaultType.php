<?php

namespace App\Form\Document\Scheme;

use App\Entity\Document\Scheme\Scheme;
use App\Entity\Document\Scheme\SchemeDefault;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SchemeDefaultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('schemeType', ChoiceType::class, [
                'choices'  => [
                    'Persoon type' => 'person',
                    'Registratie type' => 'registration',
                    'Geen type' => 'none',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SchemeDefault::class,
        ]);
    }
}
