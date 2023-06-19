<?php

namespace App\Form\Security\Import;

use Craue\FormFlowBundle\Form\FormFlow;

class ImportAccountsFlow extends FormFlow
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
                'label' => 'upload',
                'form_type' => UploadCsvType::class,
            ],
            [
                'label' => 'confirmation',
                'form_type' => ConfirmationType::class,
            ],
        ];
    }
}
