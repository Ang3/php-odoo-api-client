<?php

use Ang3\Component\Odoo\Client;

require_once __DIR__ . '/../vendor/autoload.php';


$client = new Client('URL', 'DB', 'USERNAME', 'PWD');
echo $client->authenticate();