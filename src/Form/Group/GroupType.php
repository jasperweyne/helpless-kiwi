<?php

namespace App\Form\Group;

use App\Entity\Group\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('relationable', CheckboxType::class, [
                'label' => 'Mag leden hebben',
                'required' => false,
            ])
            ->add('subgroupable', CheckboxType::class, [
                'label' => 'Mag subgroepen hebben',
                'required' => false,
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Is actief',
                'help' => 'Actieve groepen kunnen activiteiten organiseren en meer!',
                'required' => false,
            ])
            ->add('register', CheckboxType::class, [
                'label' => 'Is doelgroep',
                'help' => 'Doelgroepen kunnen geselecteerd worden als doelgroep voor activiteiten.',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }
}
