<?php

namespace App\Reflection;

use Doctrine\Instantiator\Instantiator;
use Doctrine\Persistence\Mapping\RuntimeReflectionService;

class ReflectionService extends RuntimeReflectionService
{
    /**
     * @var Instantiator
     */
    private $instantiator;

    public function __construct()
    {
        $this->instantiator = new Instantiator();
    }

    /**
     * @template T of object
     * @phpstan-param class-string<T> $classname
     *
     * @phpstan-return T
     */
    public function instantiate(string $classname, array $fieldValues = []): object
    {
        /** @var T $object */
        $object = $this->instantiator->instantiate($classname);
        $reflFields = $this->getAllProperties($classname);

        foreach ($fieldValues as $field => $value) {
            if (array_key_exists($field, $reflFields)) {
                $reflFields[$field]->setValue($object, $value);
            }
        }

        return $object;
    }

    public function getAccessibleProperty($className, $propertyName)
    {
        try {
            $reflClass = new \ReflectionClass($className);
            do {
                if ($reflClass->hasProperty($propertyName)) {
                    return parent::getAccessibleProperty($reflClass->getName(), $propertyName);
                }
            } while ($reflClass = $reflClass->getParentClass());
        } catch (\ReflectionException $e) {
        }

        return null;
    }

    public function getAllProperties(string $classname)
    {
        $reflFields = [];
        try {
            $reflClass = new \ReflectionClass($classname);
            do {
                foreach ($reflClass->getProperties() as $property) {
                    $property->setAccessible(true);
                    $reflFields[$property->getName()] = $property;
                }
            } while ($reflClass = $reflClass->getParentClass());
        } catch (\ReflectionException $e) {
        }

        return $reflFields;
    }
}
