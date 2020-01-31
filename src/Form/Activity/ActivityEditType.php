<?php

namespace App\Form\Activity;

use App\Form\Activity\Admin\ActivityEditType as DefaultActivityType;
use Symfony\Component\Form\FormBuilderInterface;

class ActivityEditType extends DefaultActivityType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('author')
        ;
    }
}
