<?php

namespace App\Form\Activity;

use App\Entity\Activity\PriceOption;
use App\Entity\Security\LocalAccount;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WaitlistSpotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('person', EntityType::class, [
                'attr' => ['data-select' => 'true'],
                'label' => 'Naam',
                'class' => LocalAccount::class,
                'choice_label' => 'canonical',
                'required' => true,
            ])
            ->add('option', EntityType::class, [
                'label' => 'Optie',
                'class' => PriceOption::class,
                'choices' => $options['allowed_options'],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'allowed_options' => [],
        ]);
    }
}
