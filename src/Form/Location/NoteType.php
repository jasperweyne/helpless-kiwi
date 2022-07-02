<?php

namespace App\Form\Location;

use App\Entity\Group\Group;
use App\Entity\Location\Note;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', TextareaType::class, ['label' => 'Notitie']);
        if (count((array) $options['groups']) > 1) {
            $builder->add('author', EntityType::class, [
                'label' => 'Auteur',
                'class' => 'App\Entity\Group\Group',
                'choices' => $options['groups'],
                'choice_label' => function (?Group $group) {
                    return $group !== null ? $group->getName() : 'Admin';
                },
                'required' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Note::class,
            'groups' => [],
        ]);
    }
}
