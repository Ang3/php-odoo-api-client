<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests\DBAL\Expression;

use Ang3\Component\Odoo\DBAL\Expression\Domain\Comparison;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\DBAL\Expression\Domain\Comparison
 *
 * @internal
 */
final class ComparisonTest extends AbstractDomainTest
{
    /**
     * @covers ::getFieldName
     * @covers ::getOperator
     * @covers ::getValue
     * @covers ::setFieldName
     * @covers ::setOperator
     * @covers ::setValue
     *
     * @throws \ReflectionException
     */
    public function testAccessorsAndMutators(): void
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

        static::assertSame(['foo', '=', 'bar'], $comparison->toArray());
    }
}
