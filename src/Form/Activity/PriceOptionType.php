<?php

namespace App\Form\Activity;

use App\Entity\Activity\PriceOption;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PriceOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => 'Standaard',
            ])
            ->add('price', MoneyType::class, [
                'divisor' => 100,
            ])
            ->add('target', EntityType::class, [
                'label' => 'Activiteit voor',
                'class' => 'App\Entity\Group\Group',
                'required' => false,
                'placeholder' => 'Iedereen',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->andWhere('t.register = TRUE');
                },
                'choice_label' => function ($ref) {
                    return $ref->getName();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PriceOption::class,
        ]);
    }
}
