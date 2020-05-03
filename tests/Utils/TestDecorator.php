<?php

namespace Ang3\Component\Odoo\Tests\Utils;

use PHPUnit\Framework\TestCase;

/**
 * @abstract
 */
abstract class TestDecorator
{
    /**
     * @var TestCase
     */
    protected $testCase;

    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function getTestCase(): TestCase
    {
        return $this->testCase;
    }

    public function setTestCase(TestCase $testCase): self
    {
        $this->testCase = $testCase;

        return $this;
    }
}
