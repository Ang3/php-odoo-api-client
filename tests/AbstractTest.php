<?php

namespace Ang3\Component\Odoo\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * @abstract
 */
abstract class AbstractTest extends TestCase
{
    use ReflectionTrait;

    /**
     * Test the getter of an object property.
     *
     * @throws ReflectionException
     *
     * @return mixed The result of the getter
     */
    public function runObjectGetterTest(object $object, string $propertyName, string $expectedType)
    {
        $class = $this->getClass($object);
        $getter = $this->generatePropertyMethodName($propertyName, 'get');

        try {
            $method = $this->getMethod($object, $getter);
        } catch (ReflectionException $e) {
            $this->fail(sprintf('The getter method "%s" is missing in class "%s"', $getter, $class->getName()));

            return null;
        }

        $this->addToAssertionCount(1);

        $result = $method->invokeArgs($object, []);

        $errorMessage = sprintf('The getter method "%s" in class %s should return %s, %s returned',
            $getter,
            $class->getName(),
            $expectedType,
            $this->debugTypeAsString($result)
        );

        $this->validateValueType($result, $expectedType, $errorMessage);

        return $result;
    }

    /**
     * Test the setter of an object property.
     *
     * @throws ReflectionException
     */
    public function runObjectSetterTest(object $object, string $propertyName, bool $isFluent = true): void
    {
        $class = $this->getClass($object);
        $setter = $this->generatePropertyMethodName($propertyName, 'set');

        try {
            $method = $this->getMethod($object, $setter);
        } catch (ReflectionException $e) {
            $this->fail(sprintf('The setter method "%s" is missing in class "%s"', $setter, $class->getName()));

            return;
        }

        $this->addToAssertionCount(1);

        $result = $method->invokeArgs($object, []);

        if ($isFluent) {
            $type = is_object($result) ? sprintf('instance of %s', get_class($result)) : gettype($result);
            $this->assertEquals($object, $result, sprintf('The setter method "%s" in class %s is fluent and should return the owner object, %s returned', $setter, $class->getName(), $type));

            return;
        }
    }

    /**
     * Validate the type of a value.
     *
     * @param mixed $value
     */
    protected function validateValueType($value, string $expectedType = null, string $errorMessage = null): void
    {
        $errorMessage = $errorMessage ?: sprintf('Expected value of type %s, %s given', $expectedType, $this->debugTypeAsString($value));

        switch ($expectedType) {
            case null:
                $this->assertNull($value, $errorMessage);
                break;

            case 'bool':
            case 'boolean':
                $this->assertIsBool($value, $errorMessage);
                break;

            case 'int':
            case 'integer':
                $this->assertIsInt($value, $errorMessage);
                break;

            case 'float':
                $this->assertIsFloat($value, $errorMessage);
                break;

            case 'string':
                $this->assertIsString($value, $errorMessage);
                break;

            case 'array':
                $this->assertIsArray($value, $errorMessage);
                break;

            case 'resource':
                $this->assertIsResource($value, $errorMessage);
                break;

            case 'object':
                $this->assertIsObject($value, $errorMessage);
                break;

            default:
                /** @var class-string $class */
                $class = $expectedType;

                $this->assertInstanceOf($class, $value, $errorMessage);
                break;
        }
    }

    /**
     * Get the type of a value as string for debugging.
     *
     * @param mixed $value
     */
    protected function debugTypeAsString($value): string
    {
        return is_object($value) ? sprintf('instance of %s', get_class($value)) : gettype($value);
    }

    /**
     * Generate the property accessor method name.
     */
    private function generatePropertyMethodName(string $propertyName, string $prefix): string
    {
        return sprintf('%s%s', $prefix, ucfirst(preg_replace('#([^A-Za-z]+)#', '', $propertyName)));
    }
}
