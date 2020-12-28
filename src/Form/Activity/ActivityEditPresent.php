<?php

namespace App\Form\Activity;

use Doctrine\ORM\EntityRepository;
use App\Entity\Activity\PriceOption;
use App\Entity\Activity\Registration;
use App\Entity\Activity\Activity;
use App\Form\Activity\PresentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ActivityEditPresent extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('registrations', CollectionType::class, [
                'entry_type' => PresentType::class,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
            'label' => false,
        ]);
    }
}