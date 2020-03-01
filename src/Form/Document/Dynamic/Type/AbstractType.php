<?php

namespace App\Form\Document\Dynamic\Type;

use App\Form\Document\Dynamic\DynamicTypeInterface;


use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

abstract class AbstractType implements DynamicTypeInterface
{
    public function getFormType(): string
    {
        return TextType::class;
    }

    public function getDefaultOptions(): array
    {
        return [];
    }

    public function getDataTransformer(): DataTransformerInterface
    {
        $identityFn = function ($x) { return $x; };

        return new CallbackTransformer($identityFn, $identityFn);
    }
}
