<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ang3\Component\Odoo\Expression\ExpressionBuilder;

$expr = new ExpressionBuilder();

$operation = $expr->andX(
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

dump($operation);
dump($operation->normalize());
dump($operation->toArray());