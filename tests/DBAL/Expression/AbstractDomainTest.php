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
use Ang3\Component\Odoo\DBAL\Expression\Domain\CompositeDomain;
use Ang3\Component\Odoo\Tests\AbstractTest;

abstract class AbstractDomainTest extends AbstractTest
{
    public function createComparison(string $operator, string $fieldName, mixed $value): Comparison
    {
        return new Comparison($operator, $fieldName, $value);
    }

    public function createCompositeDomain(string $operator, array $domains = []): CompositeDomain
    {
        return new CompositeDomain($operator, $domains);
    }
}
