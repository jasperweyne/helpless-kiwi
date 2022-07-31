<?php

namespace App\Form\Activity;

use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ActivityNewType extends ActivityEditType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image file',
                'required' => true,
                'allow_delete' => false,
            ])
        ;
    }
}
