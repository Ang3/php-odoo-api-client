<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Ang3\Component\Odoo\Query\Domain\DomainBuilder;

$domainBuilder = new DomainBuilder();

$operation = $domainBuilder->andX(
	$domainBuilder->orX(
		$domainBuilder->eq('A', 1),
		$domainBuilder->eq('B', 1),
		$domainBuilder->eq('C', 1),
		$domainBuilder->eq('D', 1)
	),
	$domainBuilder->orX(
		$domainBuilder->eq('E', 1),
		$domainBuilder->eq('F', 1),
		$domainBuilder->eq('G', 1)
	)
);

dump($operation);
dump($operation->normalize());
dump($operation->toArray());