<?php

namespace App\Form\Security;

use App\Entity\Security\LocalAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocalAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('givenname', TextType::class, [
                'required' => true,
                'label' => 'Voornaam',
            ])
            ->add('familyname', TextType::class, [
                'required' => true,
                'label' => 'Achternaam',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'E-mail',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LocalAccount::class,
        ]);
    }
}
