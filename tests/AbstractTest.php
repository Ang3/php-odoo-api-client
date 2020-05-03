<?php

namespace Ang3\Component\Odoo\Tests;

use Ang3\Component\Odoo\Tests\Utils\ObjectTester;
use Ang3\Component\Odoo\Tests\Utils\Reflector;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * @abstract
 */
abstract class AbstractTest extends TestCase
{
    /**
     * @var Reflector
     */
    protected $reflector;

    protected function setUp(): void
    {
        $this->reflector = new Reflector();
    }

    /**
     * @throws ReflectionException
     */
    protected function createObjectTester(object $object): ObjectTester
    {
        return new ObjectTester($this, $object);
    }
}
