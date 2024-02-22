<?php

namespace App\Tests;

/**
 * Test data builder.
 *
 * Usage example:
 * TestData::create(Foo::class)
 *     ->with('bar', 1, 2)
 *     ->with('qux', 3, 4)
 *     ->do('update', function($foo) { if ($foo->bar == 1 && $foo->qux == 3) $foo->bar = 5; }, null)
 *     ->where(fn($foo) => $foo->bar == 1)
 *     ->returnInvalid();
 *
 * Will return array(
 *     Foo(bar => 5, qux => 3),
 *     Foo(bar => 2, qux => 3),
 *     Foo(bar => 2, qux => 4)
 * )
 *
 * @template T of object
 */
class TestData
{
    /**
     * Create a new TestData builder from type name
     * If no argument was given, an array will be built.
     *
     * @template X of object
     *
     * @param ?class-string<X> $type
     *
     * @return TestData<X> a testdata builder for the given type
     */
    public static function create(?string $type = null): TestData
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
     * @template X of object
     *
     * @param X $object
     *
     * @return TestData<X> a testdata builder with the given object
     *
     * @throws \UnexpectedValueException if anything apart from an array or object was provided
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
     * @template TKey of array-key
     *
     * @param array<TKey, mixed> $set
     *
     * @return array<TKey, mixed>[] Cartesian product of arrays
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

    /** @var array<string, mixed[]> */
    protected $property_options;

    /** @var array<string, mixed[]> */
    protected $action_options;

    /** @var callable[] */
    protected $conditions;

    /** @var T|mixed[] */
    private $initial;

    /**
     * @param T $initial
     */
    private function __construct($initial)
    {
        $this->property_options = [];
        $this->action_options = [];
        $this->conditions = [];
        $this->initial = clone $initial;
    }

    /**
     * Add value options for a given property (or array key) to the data builder.
     *
     * @return TestData<T>
     */
    public function with(string $property, mixed ...$options): TestData
    {
        $this->property_options[$property] = array_merge($this->property_options[$property] ?? [], $options);

        return $this;
    }

    /**
     * Add optional action callables to the data builder, operating on the data.
     *
     * @return TestData<T>
     */
    public function do(string $key, ?callable ...$actions): TestData
    {
        $this->action_options[$key] = array_merge($this->action_options[$key] ?? [], $actions);

        return $this;
    }

    /**
     * Add an action callables to the data builder with multiple data options.
     *
     * @return TestData<T>
     */
    public function doWith(string $key, callable $action, mixed ...$options): TestData
    {
        $actions = [];
        foreach ($options as $option) {
            $actions[] = function ($object) use ($action, $option) {
                \call_user_func($action, $object, $option);
            };
        }

        $this->action_options[$key] = array_merge($this->action_options[$key] ?? [], $actions);

        return $this;
    }

    /**
     * Add a condition callback which tests whether a given data instance is valid
     * The callback should return a boolean value.
     *
     * @return TestData<T>
     */
    public function where(callable $condition): TestData
    {
        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * Return data instances that are valid and should result in success.
     *
     * @return T[]
     */
    public function return(): array
    {
        $permutations = [];
        foreach ($this->buildPermutations() as $perm) {
            $object = $this->buildFromData($perm);

            // If all condition succeed, add
            if ($this->allConditionsSucceed($object) && !in_array($object, $permutations)) {
                $permutations[] = $object;
            }
        }

        return $permutations;
    }

    /**
     * Return invalid data instances which may or may not fail.
     *
     * @return T[]
     */
    public function returnInvalid(): array
    {
        $permutations = [];
        foreach ($this->buildPermutations() as $perm) {
            $object = $this->buildFromData($perm);

            // If any condition fails, add
            if (!$this->allConditionsSucceed($object) && !in_array($object, $permutations)) {
                $permutations[] = $object;
            }
        }

        return $permutations;
    }

    /**
     * Build all combinations of properties and actions.
     *
     * @return array{properties: mixed[], actions: callable[]}[]
     */
    protected function buildPermutations(): array
    {
        return self::cartesian([
            'properties' => self::cartesian($this->property_options),
            'actions' => self::cartesian($this->action_options),
        ]);
    }

    /**
     * Test whether all provided conditions return successful for the given object.
     *
     * @param T $object
     */
    protected function allConditionsSucceed($object): bool
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
     *
     * @param array{properties: mixed[], actions: callable[]} $data
     *
     * @return T
     */
    protected function buildFromData(array $data)
    {
        $object = clone $this->initial;

        if (is_array($object)) {
            $object = array_merge($object, $data['properties']);
        } else {
            // For each property, assign value
            $reflector = new \ReflectionClass($object);
            foreach ($data['properties'] as $name => $value) {
                $property = $reflector->getProperty($name);
                $property->setAccessible(true);
                $property->setValue($object, $value);
            }
        }

        // Run actions on data
        foreach ($data['actions'] as $action) {
            if (is_callable($action)) {
                \call_user_func($action, $object);
            }
        }

        return $object;
    }
}
