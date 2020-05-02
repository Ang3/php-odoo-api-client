<?php

namespace Ang3\Component\Odoo\Tests;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

trait ReflectionTrait
{
    /**
     * @throws ReflectionException
     *
     * @return mixed
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
    public function getMethod($objectOrClass, string $methodName, bool $setAccessible = true): ReflectionMethod
    {
        if ($objectOrClass instanceof ReflectionClass) {
            return $objectOrClass->getMethod($methodName);
        }

        $method = new ReflectionMethod($objectOrClass, $methodName);

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
    public function getProperty($objectOrClass, string $propertyName, bool $setAccessible = true): ReflectionProperty
    {
        if ($objectOrClass instanceof ReflectionClass) {
            return $objectOrClass->getProperty($propertyName);
        }

        $property = new ReflectionProperty($objectOrClass, $propertyName);

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
            if (!class_exists($objectOrClass)) {
                throw new \RuntimeException(sprintf('The class %s was not found', $objectOrClass));
            }

            /* @var class-string $objectOrClass */
        }

        return new ReflectionClass($objectOrClass);
    }
}
