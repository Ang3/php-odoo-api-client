<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Inflector\Inflector;

class ObjectTester extends TestDecorator
{
    /**
     * Accessors and mutators names list.
     */
    public const ACCESSOR_GET = 'get';
    public const ACCESSOR_HAS = 'has';
    public const MUTATOR_SET = 'set';
    public const MUTATOR_ADD = 'add';
    public const MUTATOR_REMOVE = 'remove';

    /**
     * Context parameter keys.
     */
    public const NAME = 'name';
    public const VALUE = 'value';
    public const RESULT = 'result';
    public const MESSAGE = 'message';
    public const IS_FLUENT = 'is_fluent';
    public const IS_COLLECTION = 'is_collection';

    private object $object;
    private \ReflectionClass $class;
    private Reflector $reflector;

    private array $defaultContext = [
        self::NAME => null,
        self::VALUE => null,
        self::RESULT => null,
        self::MESSAGE => null,
        self::IS_FLUENT => false,
        self::IS_COLLECTION => false,
    ];

    /**
     * @throws \ReflectionException
     */
    public function __construct(TestCase $testCase, object $object, array $defaultContext = [])
    {
        parent::__construct($testCase);

        $this->reflector = new Reflector();
        $this->setObject($object);
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * Test the accessors of a property (setter and getter).
     * This test also checks if the return value of the getter is equal to the value registered with the setter.
     */
    public function assertPropertyAccessorsAndMutators(string $propertyName, mixed $value, array $context = []): self
    {
        $context = $this->getContext($context);
        $context[self::VALUE] = $value;
        $context[self::MESSAGE] = sprintf('Asserting accessors and mutators for property %s::$%s', $this->class->getShortName(), $propertyName);

        if ($context[self::IS_COLLECTION]) {
            $adderContext = $this->getContext($context['adder'] ?? [], $context);
            $adder = $this->assertAdder($propertyName, $value, $adderContext);

            $hasserContext = $this->getContext($context['hasser'] ?? [], $context);
            $hasser = $this->assertHasser($propertyName, $value, $hasserContext);

            $removerContext = $this->getContext($context['remover'] ?? [], $context);
            $remover = $this->assertRemover($propertyName, $value, $removerContext);
        }

        $setterContext = $this->getContext($context['setter'] ?? [], $context);
        $setter = $this->assertSetter($propertyName, $value, $setterContext);

        $getterContext = $this->getContext($context['getter'] ?? [], $context);
        $getter = $this->assertGetter($propertyName, $getterContext);

        return $this;
    }

    public function assertHasser(string $propertyName, mixed $value, array $context = []): ?\ReflectionMethod
    {
        $context[self::VALUE] = $value;
        $context[self::IS_FLUENT] = false;
        $context[self::MESSAGE] = sprintf(
            'Asserting hasser for property %s::$%s',
            $this->class->getShortName(),
            $propertyName
        );

        return $this->assertPropertyMethod($propertyName, self::ACCESSOR_HAS, $context);
    }

    public function assertGetter(string $propertyName, array $context = []): ?\ReflectionMethod
    {
        $context[self::IS_FLUENT] ??= false;
        $context[self::MESSAGE] = sprintf(
            'Asserting getter for property %s::$%s',
            $this->class->getShortName(),
            $propertyName
        );

        return $this->assertPropertyMethod($propertyName, self::ACCESSOR_GET, $context);
    }

    public function assertAdder(string $propertyName, mixed $value, array $context = []): ?\ReflectionMethod
    {
        $context[self::VALUE] = $value;
        $context[self::IS_FLUENT] ??= true;
        $context[self::MESSAGE] = sprintf(
            'Asserting adder for property %s::$%s',
            $this->class->getShortName(),
            $propertyName
        );

        return $this->assertPropertyMethod($propertyName, self::MUTATOR_ADD, $context);
    }

    public function assertRemover(string $propertyName, mixed $value, array $context = []): ?\ReflectionMethod
    {
        $context[self::VALUE] = $value;
        $context[self::IS_FLUENT] ??= true;
        $context[self::MESSAGE] = sprintf(
            'Asserting remover for property %s::$%s',
            $this->class->getShortName(),
            $propertyName
        );

        return $this->assertPropertyMethod($propertyName, self::MUTATOR_REMOVE, $context);
    }

    /**
     * Test and return the setter of a property.
     */
    public function assertSetter(string $propertyName, mixed $value = null, array $context = []): ?\ReflectionMethod
    {
        $context[self::VALUE] = $value;
        $context[self::IS_FLUENT] ??= true;
        $context[self::MESSAGE] = sprintf(
            'Asserting setter for property %s::$%s',
            $this->class->getShortName(),
            $propertyName
        );

        return $this->assertPropertyMethod($propertyName, self::MUTATOR_SET, $context);
    }

    /**
     * Test and return the specific method of a property.
     */
    public function assertPropertyMethod(string $propertyName, string $prefix, array $context = []): ?\ReflectionMethod
    {
        $context = $this->getContext($context);

        try {
            $property = $this->reflector->getProperty($this->class, $propertyName);
        } catch (\ReflectionException $e) {
            $this->testCase::fail($this->getContextErrorMessage('The property was not found', $context));
        }

        $context[self::NAME] = (string) ($context[self::NAME] ?? null);
        $methodName = $context[self::NAME] ?: $this->getPropertyMethodName($property->getName(), $prefix);
        $class = $property->getDeclaringClass();
        $tested = [];

        if (!$context[self::NAME] && $context[self::IS_COLLECTION]) {
            $methodNames = $this->getSingularNames($property->getName(), $prefix);

            foreach ($methodNames as $value) {
                if ($class->hasMethod($value)) {
                    $methodName = $value;
                    break;
                }

                $tested[] = $value;
            }
        } else {
            $tested[] = $methodName;
        }

        try {
            $method = $class->getMethod($methodName);
        } catch (\ReflectionException $e) {
            $errorMessage = sprintf('None of methods "%s()" was found', implode('"(), "', $tested));
            $this->testCase::fail($this->getContextErrorMessage($errorMessage, $context));
        }

        $this->testCase->addToAssertionCount(1);

        $args = self::MUTATOR_SET === $prefix && $context[self::IS_COLLECTION] ? [$context[self::VALUE]] : $context[self::VALUE];
        $args = self::ACCESSOR_GET === $prefix ? [] : [$args];
        $result = $method->invokeArgs($this->object, $args);

        if ((bool) ($context[self::IS_FLUENT] ?? false)) {
            $this->testCase::assertEquals($this->object, $result, $this->getContextErrorMessage(
                'The method is fluent and should return the object instance',
                $context
            ));

            return $method;
        }

        $propertyValue = $property->getValue($this->object);

        switch ($prefix) {
            case self::ACCESSOR_GET:
                $this->testCase::assertEquals($result, $propertyValue);
                break;

            case self::MUTATOR_SET:
                $expectedValue = $context[self::IS_COLLECTION] ? [$context[self::VALUE]] : $context[self::VALUE];
                $this->testCase::assertEquals($expectedValue, $propertyValue, $this->getContextErrorMessage(
                    'The property value is not equal to the value set',
                    $context
                ));
                break;

            case self::MUTATOR_ADD:
            case self::MUTATOR_REMOVE:
            case self::ACCESSOR_HAS:
                if (!is_iterable($propertyValue)) {
                    $this->testCase::fail($this->getContextErrorMessage(sprintf(
                        'The property collection should be iterable, %s declared',
                        get_debug_type($propertyValue)
                    ), $context));
                }

                $hasValue = false;

                foreach ($propertyValue as $value) {
                    if ($value === $context[self::VALUE]) {
                        $hasValue = true;
                        break;
                    }
                }

                switch ($prefix) {
                    case self::ACCESSOR_HAS:
                        $this->testCase::assertEquals($result, $hasValue, $this->getContextErrorMessage(
                            sprintf(
                                'The property collection %s the value but the hasser returns %s',
                                $hasValue ? 'contains' : 'does not contain',
	                            $result ? 'TRUE' : 'FALSE'
                            ),
                            $context
                        ));
                        break;
                    case self::MUTATOR_ADD:
                        $this->testCase::assertTrue($hasValue, $this->getContextErrorMessage(
                            'The adder was called but the property collection does not contain the added value',
                            $context
                        ));

                        return $method;

                    case self::MUTATOR_REMOVE:
                        $this->testCase::assertFalse($hasValue, $this->getContextErrorMessage(
                            'The property collection still contains the removed value',
                            $context
                        ));

                        return $method;
                }
                break;
        }

        return $method;
    }

    public function getContextErrorMessage(string $message, array $context = []): string
    {
        $context = $this->getContext($context);

        return sprintf('%s%s', $context[self::MESSAGE] ? sprintf('[%s] ', $context[self::MESSAGE]) : '', $message);
    }

    /**
     * @return string[]
     */
    public function getSingularNames(string $name, string $prefix = null): array
    {
        $names = (array) Inflector::singularize($name);

        if ($prefix) {
            foreach ($names as $key => $value) {
                $names[$key] = $this->getPropertyMethodName($value, $prefix);
            }
        }

        return $names;
    }

    public function getPropertyMethodName(string $propertyName, string $prefix = ''): string
    {
        $propertyName = preg_replace('#([^A-Za-z]+)#', '', $propertyName);

        return sprintf('%s%s', $prefix, $prefix ? ucfirst($propertyName) : $propertyName);
    }

    public function getObject(): object
    {
        return $this->object;
    }

    /**
     * @throws \ReflectionException
     */
    public function setObject(object $object): self
    {
        $this->object = $object;
        $this->class = $this->reflector->getClass($object);

        return $this;
    }

    public function getClass(): \ReflectionClass
    {
        return $this->class;
    }

    public function getReflector(): Reflector
    {
        return $this->reflector;
    }

    public function setReflector(Reflector $reflector): self
    {
        $this->reflector = $reflector;

        return $this;
    }

    public function getDefaultContext(): array
    {
        return $this->defaultContext;
    }

    public function setDefaultContext(array $defaultContext): self
    {
        $this->defaultContext = $defaultContext;

        return $this;
    }

    public function getContext(array $context = [], array $defaultContext = []): array
    {
        return array_merge($defaultContext ?: $this->defaultContext, $context);
    }
}
