<?php

namespace App\Form\Claim;

use App\Entity\Claim\Claim;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ClaimType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description')
            ->add('purpose')
            ->add('price')
            ->add('imageFile', VichImageType::class, [
                'required' => true,
                'allow_delete' => false,
            ])
            // ->add('createdAt')
            // ->add('author')
            // ->add('reviewedBy')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Claim::class,
        ]);
    }
}
