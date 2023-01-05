<?php

namespace App\Form\Activity;

use App\Entity\Activity\Activity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityEditPresent extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('registrations', CollectionType::class, [
                'entry_type' => PresentType::class,
                'label' => false,
            ])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $registrationView = $view['registrations'];
        assert($registrationView !== null);
        \usort($registrationView->children, fn (FormView $a, FormView $b) => $a->vars['data']->getPerson()->getCanonical() <=> $b->vars['data']->getPerson()->getCanonical());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
            'label' => false,
        ]);
    }
}
