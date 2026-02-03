<?php

declare(strict_types=1);

if (PHP_VERSION_ID < 80100 || PHP_VERSION_ID >= 80200) {
    fwrite(STDERR, "PHP CS Fixer's config for PHP-HIGHEST can be executed only on highest supported PHP version - 8.1.*.\n");
    fwrite(STDERR, "Running it on lower PHP version would prevent calling migration rules.\n");

    exit(1);
}

$config = require __DIR__.'/.php-cs-fixer.dist.php';

$config->setRules(array_merge($config->getRules(), [
    '@PHP81Migration' => true,
    '@PHP80Migration:risky' => true,
    'heredoc_indentation' => false,
]));

return $config;
