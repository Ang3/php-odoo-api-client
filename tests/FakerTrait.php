<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Tests;

use Faker\Factory;
use Faker\Generator;

trait FakerTrait
{
    protected static Generator|null $faker = null;

    public static function faker(): Generator
    {
        if (!self::$faker) {
            self::$faker = Factory::create('fr');
        }

        return self::$faker;
    }
}
