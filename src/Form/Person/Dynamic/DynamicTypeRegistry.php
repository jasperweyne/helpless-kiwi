<?php

namespace App\Form\Person\Dynamic;

use Doctrine\Common\Collections\ArrayCollection;

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

    public function getTypeNames(): array
    {
        return $this->types->getKeys();
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
