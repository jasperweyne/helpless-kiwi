<?php

namespace Tests\Helper;

/**
 * Test data builder.
 *
 * Usage example:
 * TestData::create(Foo::class)
 *     ->with('bar', [1, 2])
 *     ->with('qux', [3, 4])
 *     ->where(fn($foo) => $foo->bar == 1)
 *     ->getFailureData();
 *
 * Will return array(
 *     Foo(bar => 2, qux => 3),
 *     Foo(bar => 2, qux => 4)
 * )
 */
class TestData
{
    /**
     * Create a new TestData builder from type name
     * If no argument was given, an array will be built.
     *
     * @return TestData a testdata builder for the given type
     */
    public static function create(string $type = null): TestData
    {
        if (is_null($type)) {
            return new TestData([]);
        }

        // Instantiate object without constructor (otherwise, self::from can be used)
        $reflector = new \ReflectionClass($type);
        $object = $reflector->newInstanceWithoutConstructor();

        // Assign default properties
        foreach ($reflector->getDefaultProperties() as $name => $default) {
            $property = $reflector->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($object, $default);
        }

        return new TestData($object);
    }

    /**
     * Create a new TestData builder from an existing object.
     *
     * @throws UnexpectedValueException if anything apart from an array or object was provided
     *
     * @return TestData a testdata builder with the given object
     */
    public static function from($object): TestData
    {
        if (!is_object($object) && !is_array($object)) {
            throw new \UnexpectedValueException('Only arrays or objects are supported');
        }

        return new TestData($object);
    }

    /**
     * Builds a cartesian product for an array of arrays.
     *
     * @return array Cartesian product of arrays
     */
    protected static function cartesian($set): array
    {
        if (!$set) {
            return [[]];
        }

        $key = array_key_last($set);
        $subset = array_pop($set);
        $cartesianSubset = self::cartesian($set);

        $result = [];
        foreach ($subset as $value) {
            foreach ($cartesianSubset as $p) {
                $p[$key] = $value;
                $result[] = $p;
            }
        }

        return $result;
    }

    protected $property_options;

    protected $conditions;

    private $initial;

    private function __construct(mixed $initial)
    {
        $this->property_options = [];
        $this->conditions = [];
        $this->initial = clone $initial;
    }

    /**
     * Add value options for a given property (or array key) to the data builder.
     */
    public function with(string $property, array $options): TestData
    {
        $this->property_options[$property] = array_merge($this->property_options[$property] ?? [], $options);

        return $this;
    }

    /**
     * Add a condition callback which tests whether a given data instance is valid
     * The callback should return a boolean value.
     */
    public function where(callable $condition): TestData
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * Return data instances that are valid and should result in success.
     */
    public function getValidData(): array
    {
        $permutations = [];
        foreach (self::cartesian($this->property_options) as $perm) {
            $object = $this->buildFromData($perm);

            // If all condition succeed, add
            if ($this->allConditionsSucceed($object)) {
                $permutations[] = $object;
            }
        }

        return $permutations;
    }

    /**
     * Return invalid data instances which may or may not fail.
     */
    public function getFailureData(): array
    {
        $permutations = [];
        foreach (self::cartesian($this->property_options) as $perm) {
            $object = $this->buildFromData($perm);

            // If any condition fails, add
            if (!$this->allConditionsSucceed($object)) {
                $permutations[] = $object;
            }
        }

        return $permutations;
    }

    /**
     * Test whether all provided conditions return succesful for the given object.
     */
    protected function allConditionsSucceed(mixed $object): bool
    {
        foreach ($this->conditions as $callback) {
            if (!\call_user_func($callback, $object)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build an array or object, based on the initial object from raw array data.
     */
    protected function buildFromData(array $data): mixed
    {
        if (is_array($this->initial)) {
            return array_merge($this->initial, $data);
        }

        // For each property, assign value
        $reflector = new \ReflectionClass($this->initial);
        $object = clone $this->initial;
        foreach ($data as $name => $value) {
            $property = $reflector->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($object, $value);
        }

        return $object;
    }
}
