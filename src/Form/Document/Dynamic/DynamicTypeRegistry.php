<?php

namespace App\Form\Document\Dynamic;

use Doctrine\Common\Collections\ArrayCollection;
use App\Form\Document\Dynamic\DynamicTypeInterface;


class DynamicTypeRegistry
{
    private $types;

    public function __construct($iterator)
    {
        $values = iterator_to_array($iterator);

        $keys = array_map(function (DynamicTypeInterface $x) {
            return $x->getName();
        }, $values);

        $this->types = new ArrayCollection(array_combine($keys, $values));
    }

    public function getTypes(): array
    {
        return $this->types->toArray();
    }

    public function get(string $name): DynamicTypeInterface
    {
        if (!$this->types->containsKey($name)) {
            throw new \UnexpectedValueException($name.' not a valid type name');
        }

        return $this->types[$name];
    }

    public function has(string $name): bool
    {
        return $this->types->containsKey($name);
    }
}
