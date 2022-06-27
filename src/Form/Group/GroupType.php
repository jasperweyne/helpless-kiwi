<?php

namespace App\Form\Group;

use App\Entity\Group\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description', TextareaType::class, [
                'required' => false,
            ])
            ->add('contactFields', EmailType::class, [
                'label' => 'Contact e-mailadres',
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

        $builder->get('contactFields')
            ->addModelTransformer(new CallbackTransformer(
                function ($emailAsArray) {
                    // transform the array to a string
                    return str_replace('mailto:', '', $emailAsArray['email'] ?? '');
                },
                function ($emailAsString) {
                    // transform the string back to an array
                    if ($emailAsString) {
                        return ['email' => "mailto:$emailAsString"];
                    }

                    return [];
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }
}
