<?php

namespace App\Form\Activity;

use App\Entity\Activity\Registration;
use App\Entity\Security\LocalAccount;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (true === ($options['external_registrant'] ?? false)) {
            $builder->add('person', ExternalRegistrantType::class);
        } else {
            $builder->add('person', EntityType::class, [
                'attr' => ['data-select' => 'true'],
                'label' => 'Naam',
                'class' => LocalAccount::class,
                'choice_label' => 'canonical',
                'required' => true,
            ]);
        }

        $builder
            ->add('option', EntityType::class, [
                'label' => 'Optie',
                'class' => 'App\Entity\Activity\PriceOption',
                'choices' => $options['allowed_options'],
                'choice_label' => function ($ref) {
                    return $ref;
                },
                'required' => true,
            ])
            ->add('comment', TextType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Registration::class,
            'allowed_options' => [],
            'external_registrant' => false,
        ]);
    }
}
