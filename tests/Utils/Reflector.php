<?php

namespace Ang3\Component\Odoo\Tests\Utils;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

class Reflector
{
    /**
     * @return mixed
     *
     * @throws ReflectionException
     */
    public function getObjectValue(object $object, string $propertyName)
    {
        return $this->getProperty($object, $propertyName)->getValue($object);
    }

    /**
     * @param mixed $value
     *
     * @throws ReflectionException
     */
    public function setObjectValue(object $object, string $propertyName, $value): ReflectionProperty
    {
        $property = $this->getProperty($object, $propertyName);

        $property->setValue($object, $value);

        return $property;
    }

    /**
     * @param object|string $objectOrClass
     *
     * @throws ReflectionException
     */
    public function getMethod($objectOrClass, string $methodName, bool $setAccessible = true): ?ReflectionMethod
    {
        $class = $this->getClass($objectOrClass);
        $method = $class->getMethod($methodName);

        if ($setAccessible) {
            $method->setAccessible(true);
        }

        return $method;
    }

    /**
     * @param object|string $objectOrClass
     *
     * @throws ReflectionException
     */
    public function getProperty($objectOrClass, string $propertyName, bool $setAccessible = true): ?ReflectionProperty
    {
        $class = $this->getClass($objectOrClass);
        $property = $class->getProperty($propertyName);

        if ($setAccessible) {
            $property->setAccessible(true);
        }

        return $property;
    }

    /**
     * @param object|string $objectOrClass
     *
     * @throws ReflectionException
     */
    public function getClass($objectOrClass): ReflectionClass
    {
        if (is_string($objectOrClass)) {
            /* @var class-string $class */
            $class = $objectOrClass;

            return new ReflectionClass($class);
        }

        return $objectOrClass instanceof ReflectionClass ? $objectOrClass : new ReflectionClass($objectOrClass);
    }
}
