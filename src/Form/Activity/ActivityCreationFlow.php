<?php

namespace App\Form\Activity;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;

class ActivityCreationFlow extends FormFlow
{
    /**
     * @return array{
     *   label?: string,
     *   form_type?: class-string,
     *   form_options?: mixed[],
     *   skip?: bool|callable
     * }[]
     */
    protected function loadStepsConfig()
    {
        return [
            [
                'label' => 'create activity',
                'form_type' => ActivityNewType::class,
            ],
            [
                'label' => 'create location',
                'form_type' => ActivityLocationType::class,
                'skip' => function ($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    assert($flow->getFormData() instanceof ActivityCreationData);

                    return $estimatedCurrentStepNumber > 1 && null !== $flow->getFormData()->activity->getLocation();
                },
            ],
        ];
    }
}
