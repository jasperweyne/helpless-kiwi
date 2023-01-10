<?php

namespace App\Form\Group;

use App\Entity\Security\LocalAccount;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class RelationType extends AbstractType
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
        ;
    }
}
