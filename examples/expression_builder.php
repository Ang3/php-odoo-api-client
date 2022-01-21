<?php

/**
 * This example reproduces the explanation of Ray Carnes in the followed link.
 *
 * @see https://www.odoo.com/fr_FR/forum/aide-1/question/domain-notation-using-multiple-and-nested-and-2170
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Ang3\Component\Odoo\DBAL\Expression\ExpressionBuilder;

$expr = new ExpressionBuilder();

$domain = $expr->andX(
	$expr->orX(
		$expr->eq('A', 1),
		$expr->eq('B', 1)
	),
	$expr->orX(
		$expr->eq('C', 1),
		$expr->eq('D', 1),
		$expr->eq('E', 1)
	)
);

dump($domain);
dump($domain->toArray());

// Expected: [ '&', '|', (A), (B), '|', (C), '|', (D), (E) ]