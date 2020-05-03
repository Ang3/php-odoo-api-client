<?php

namespace Ang3\Component\Odoo\Tests\Expression;

use Ang3\Component\Odoo\Expression\Comparison;
use ReflectionException;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\Expression\Comparison
 */
class ComparisonTest extends AbstractDomainTest
{
    /**
     * @covers ::setFieldName
     * @covers ::getFieldName
     * @covers ::setOperator
     * @covers ::getOperator
     * @covers ::setValue
     * @covers ::getValue
     *
     * @throws ReflectionException
     */
    public function testAccessors(): void
    {
        $comparison = new Comparison('foo', Comparison::EQUAL_TO, 'bar');

        $this
            ->createObjectTester($comparison)
            ->assertPropertyAccessorsAndMutators('fieldName', 'bar')
            ->assertPropertyAccessorsAndMutators('operator', Comparison::NOT_EQUAL_TO)
            ->assertPropertyAccessorsAndMutators('value', 'mixed')
        ;
    }

    /**
     * @covers ::toArray
     */
    public function testToArray(): void
    {
        $comparison = new Comparison('foo', Comparison::EQUAL_TO, 'bar');

        $this->assertEquals(['foo', '=', 'bar'], $comparison->toArray());
    }
}
