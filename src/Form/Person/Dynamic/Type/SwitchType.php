<?php

namespace App\Form\Person\Dynamic\Type;

use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SwitchType extends AbstractType
{
    public function getName(): string
    {
        return 'switch';
    }

    public function getFormType(): string
    {
        return CheckboxType::class;
    }

    public function getDefaultOptions(): array
    {
        return [
            'required' => false,
        ];
    }

    public function getDataTransformer(): DataTransformerInterface
    {
        return new CallbackTransformer(
            function ($encodedToBool) {
                // transform the encoded value back to a boolean
                return json_decode($encodedToBool);
            },
            function ($switchToString) {
                // transform the form value to a string
                return json_encode($switchToString);
            }
        );
    }
}
