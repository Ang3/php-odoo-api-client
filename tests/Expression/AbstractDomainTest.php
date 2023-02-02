<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests\Expression;

use Ang3\Component\Odoo\DBAL\Expression\Comparison;
use Ang3\Component\Odoo\Expression\CompositeDomain;
use Ang3\Component\Odoo\Tests\AbstractTest;

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
