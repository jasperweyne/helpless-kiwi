<?php

namespace App\Form\Activity;

use App\Form\Activity\Admin\ActivityNewType as DefaultActivityType;
use Symfony\Component\Form\FormBuilderInterface;

class ActivityNewType extends DefaultActivityType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('author')
        ;
    }
}
