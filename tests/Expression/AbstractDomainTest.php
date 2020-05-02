<?php

namespace Ang3\Component\Odoo\Tests\Expression;

use Ang3\Component\Odoo\Expression\Comparison;
use Ang3\Component\Odoo\Expression\CompositeDomain;
use Ang3\Component\Odoo\Tests\AbstractTest;

/**
 * @abstract
 */
abstract class AbstractDomainTest extends AbstractTest
{
    /**
     * @param mixed $value
     */
    public function createComparison(string $operator, string $fieldName, $value): Comparison
    {
        return new Comparison($operator, $fieldName, $value);
    }

    public function createCompositeDomain(string $operator, array $domains = []): CompositeDomain
    {
        return new CompositeDomain($operator, $domains);
    }
}
