<?php

namespace Ang3\Component\Odoo\Tests\Expression;

use Ang3\Component\Odoo\Expression\CustomDomain;
use ReflectionException;

/**
 * @coversDefaultClass \Ang3\Component\Odoo\Expression\CustomDomain
 */
class CustomDomainTest extends AbstractDomainTest
{
    /**
     * @covers ::setExpr
     * @covers ::getExpr
     *
     * @throws ReflectionException
     */
    public function testAccessorsAndMutators(): void
    {
        $domain = new CustomDomain($data = ['foo', 'bar', 'baz']);

        $this
            ->createObjectTester($domain)
            ->assertPropertyAccessorsAndMutators('expr', ['baz', 'bar', 'foo'])
        ;
    }

    /**
     * @covers ::toArray
     */
    public function testToArray(): void
    {
        $domain = new CustomDomain($data = ['foo', 'bar', 'baz']);

        $this->assertEquals($data, $domain->toArray());
    }
}
