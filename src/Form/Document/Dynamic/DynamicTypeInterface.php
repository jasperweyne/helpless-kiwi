<?php

namespace App\Form\Document\Dynamic;

use Symfony\Component\Form\DataTransformerInterface;

interface DynamicTypeInterface
{
    public function getName(): string;

    public function getFormType(): string;

    public function getDefaultOptions(): array;

    public function getDataTransformer(): DataTransformerInterface;
}
