<?php

namespace App\Form\Activity;

use Doctrine\ORM\EntityRepository;
use App\Entity\Activity\PriceOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PriceOptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => 'Standaard'
            ])
            ->add('price', MoneyType::class, [
                'divisor' => 100,
            ])
            ->add('target', EntityType::class, [
                'label' => 'Activiteit voor',
                'class' => 'App\Entity\Group\Group',
                'required' => false,
                'placeholder' => "Iedereen",
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->andWhere('t.active = TRUE');
                },
                'choice_label' => function ($ref) {
                    return $ref->getName();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PriceOption::class,
        ]);
    }
}
