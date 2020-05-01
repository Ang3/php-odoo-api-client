<?php

namespace Ang3\Component\Odoo\Exception;

class RemoteException extends RequestException
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private $traceBack = [];

    public static function createFromXmlResult(int $faultCode, string $faultString): self
    {
        $exception = new self($faultString, $faultCode);

        $messages = array_filter(explode("\n", $faultString));
        foreach ($messages as $key => $message) {
            $messages[$key] = trim($message);
        }

        array_shift($messages);
        $messages = array_values($messages);
        $messageParts = [];
        $trace = [];

        foreach ($messages as $i => $value) {
            if (preg_match('#^File "(.*)", line (\d+), in (.*)#', $messages[$i], $matches)) {
                $exception = new self($messages[$i + 1], $faultCode, $exception);

                $trace[] = [
                    'file' => $matches[1],
                    'line' => (int) $matches[2],
                    'method' => $matches[3],
                    'statement' => $messages[$i + 1],
                ];

                ++$i;

                continue;
            }

            if (preg_match('#\w+#', $messages[$i])) {
                $messageParts[] = $value;
            }
        }

        $message = $trace ? implode("\n", $messageParts) : $faultString;

        $exception = new self($message, $faultCode);
        $exception->traceBack = array_reverse($trace);

        return $exception;
    }

    public function getTraceBack(): array
    {
        return $this->traceBack;
    }
}
