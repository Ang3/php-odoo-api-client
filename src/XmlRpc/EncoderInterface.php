<?php

namespace Ang3\Component\Odoo\XmlRpc;

use InvalidArgumentException;
use PhpXmlRpc\Value;

interface EncoderInterface
{
    /**
     * @param mixed $value
     *
     * @throws InvalidArgumentException when the type of the value is not supported
     */
    public function encode($value, string $type = null, array $context = []): Value;

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function decode($data);
}
