<?php

namespace App\Form\Location;

use App\Entity\Location\Location;
use App\Form\Delete\LocationDeleteData;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LocationDeleteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $location = $options['location'];
        if (null === $location || !($location instanceof Location)) {
            throw new \LogicException('A location must be provided for a LocationDeleteType form');
        }

        $activityCount = $location->getActivities()->count();
        $builder
            ->add('activity', EntityType::class, [
                'label' => "Vervang de locatie uit {$activityCount} activiteiten met",
                'class' => Location::class,
                'choice_label' => 'address',
                'query_builder' => function (EntityRepository $repo) use ($location) {
                    return $repo->createQueryBuilder('l')
                        ->where('l != :self')
                        ->orderBy('l.address', 'ASC')
                        ->setParameter('self', $location);
                },
                'attr' => ['data-select' => 'true'],
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LocationDeleteData::class,
            'location' => null,
        ]);
    }
}
