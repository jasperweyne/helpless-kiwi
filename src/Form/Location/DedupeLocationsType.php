<?php

namespace App\Form\Location;

use App\Entity\Location\Location;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DedupeLocationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['locations'] as $location) {
            assert($location instanceof Location);

            $builder
                ->add($location->getId(), ChoiceType::class, [
                    'choices' => ['Samenvoegen' => true, 'Niet samenvoegen' => false],
                    'data' => false,
                    'label' => $location->getAddress(),
                    'required' => true,
                    'expanded' => true,
                ])
            ;
        };
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'locations' => [],
        ]);
    }
}
