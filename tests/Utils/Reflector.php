<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests\Utils;

class Reflector
{
    /**
     * @throws \ReflectionException
     */
    public function getObjectValue(object $object, string $propertyName): mixed
    {
        return $this->getProperty($object, $propertyName)->getValue($object);
    }

    /**
     * @throws \ReflectionException
     */
    public function setObjectValue(object $object, string $propertyName, mixed $value): \ReflectionProperty
    {
        $property = $this->getProperty($object, $propertyName);

        $property->setValue($object, $value);

        return $property;
    }

    /**
     * @throws \ReflectionException
     */
    public function getMethod(object|string $objectOrClass, string $methodName, bool $setAccessible = true): ?\ReflectionMethod
    {
        $class = $this->getClass($objectOrClass);

	    return $class->getMethod($methodName);
    }

    /**
     * @throws \ReflectionException
     */
    public function getProperty(object|string $objectOrClass, string $propertyName, bool $setAccessible = true): ?\ReflectionProperty
    {
        $class = $this->getClass($objectOrClass);

	    return $class->getProperty($propertyName);
    }

    /**
     * @throws \ReflectionException
     */
    public function getClass(object|string $objectOrClass): \ReflectionClass
    {
        if (\is_string($objectOrClass)) {
			if (!class_exists($objectOrClass)) {
				throw new \RuntimeException(sprintf('The class "%s" was not found.', $objectOrClass));
			}

            return new \ReflectionClass($objectOrClass);
        }

        return $objectOrClass instanceof \ReflectionClass ? $objectOrClass : new \ReflectionClass($objectOrClass);
    }
}
