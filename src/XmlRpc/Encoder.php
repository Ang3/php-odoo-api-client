<?php

namespace Ang3\Component\Odoo\XmlRpc;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use PhpXmlRpc\Value;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class Encoder implements EncoderInterface
{
    /**
     * Default configuration.
     */
    public const TIMEZONE = 'timezone';
    public const DEFAULT_TIMEZONE = 'UTC';

    /**
     * @var XmlEncoder
     */
    private $xmlEncoder;

    /**
     * @var array
     */
    private $defaultContext = [
        self::TIMEZONE => self::DEFAULT_TIMEZONE,
    ];

    public function __construct(array $defaultContext = [])
    {
        $this->xmlEncoder = new XmlEncoder();
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * @param mixed $value
     *
     * @throws InvalidArgumentException when the type of the value is not supported
     */
    public function encode($value, string $type = null, array $context = []): Value
    {
        $context = array_merge($this->defaultContext, $context);

        if ($value instanceof Value) {
            return $value;
        }

        if ($value instanceof DateTime) {
            $type = 'string';
            $value = $value
                ->setTimezone(new DateTimeZone($context['timezone'] ?? self::DEFAULT_TIMEZONE))
                ->format('Y-m-d H:i:s');
        }

        $type = $type ?: gettype($value);

        switch ($type) {
            case 'boolean':
                return new Value($value, Value::$xmlrpcBoolean);
            case 'integer':
                return new Value($value, Value::$xmlrpcInt);
            case 'double':
                return new Value($value, Value::$xmlrpcDouble);
            case 'string':
                return new Value($value, Value::$xmlrpcString);
            case 'array':
                $type = Value::$xmlrpcArray;

                foreach ($value as $key => $row) {
                    if (!is_int($key)) {
                        return $this->encode($value, 'struct');
                    }

                    $value[$key] = $this->encode($row);
                }

                return new Value($value, $type);
            case 'struct':
                $type = Value::$xmlrpcStruct;

                foreach ($value as $key => $row) {
                    $value[(string) $key] = $this->encode($row);
                }

                return new Value($value, $type);

            default:
                throw new InvalidArgumentException(sprintf('The type "%s" is not supported', $type));
        }
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function decode($data)
    {
        $data = is_string($data) ? $this->xmlEncoder->decode($data, 'xml') : $data;

        if (is_array($data)) {
            if (isset($data['value'])) {
                return $this->decodeValue($data['value']);
            }

            foreach ($data as $key => $value) {
                if (!is_array($value)) {
                    continue;
                }

                $data[$key] = $this->decode($value);
            }
        }

        return $data;
    }

    /**
     * @return mixed
     */
    public function decodeValue(array $data)
    {
        if ($data) {
            if (isset($data[0])) {
                foreach ($data as $key => $value) {
                    $data[$key] = $this->decodeTypedValue($value, (string) $key);
                }

                return $data;
            }

            $type = array_keys($data)[0];
            $value = array_pop($data);

            if (null !== $value) {
                return $this->decodeTypedValue($value, (string) $type);
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function decodeTypedValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'int':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'array':
                return $this->decodeArrayValue($value);
            case 'struct':
                return $this->decodeStructValue($value);
        }
    }

    public function decodeArrayValue(array $data): array
    {
        $values = $data['data']['value'] ?? [];
        $values = isset($values[0]) ? $values : [$values];

        foreach ($values as $key => $value) {
            $values[$key] = $this->decodeValue($value);
        }

        return $values;
    }

    public function decodeStructValue(array $data): array
    {
        $members = $data['member'] ?? [];
        $values = [];

        foreach ($members as $member) {
            $values[$member['name']] = $this->decodeValue($member['value']);
        }

        return $values;
    }

    public function getXmlEncoder(): XmlEncoder
    {
        return $this->xmlEncoder;
    }
}
