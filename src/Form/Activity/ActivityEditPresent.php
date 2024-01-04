<?php

namespace App\Form\Activity;

use App\Entity\Activity\Activity;
use App\Entity\Activity\Registration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityEditPresent extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentRegistrations', CollectionType::class, [
                'entry_type' => PresentType::class,
                'label' => false,
            ])
        ;
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        // Get registrations field
        $registrationView = $view['currentRegistrations'];
        assert(null !== $registrationView->children);

        // Assign person as label
        foreach ($registrationView->children as $childView) {
            $registration = $childView->vars['data'];
            assert($registration instanceof Registration);
            $childView->vars['label'] = (string) $registration->getPerson();
        }

        // Sort the children by label
        \usort($registrationView->children, fn (FormView $a, FormView $b) => $a->vars['label'] <=> $b->vars['label']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
            'label' => false,
        ]);
    }
}
