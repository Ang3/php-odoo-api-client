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

        if (preg_match('#Traceback \(most recent call last\)#', $faultString)) {
            $messages = array_filter(explode("\n", $faultString));

            foreach ($messages as $key => $message) {
                $messages[$key] = trim($message);
            }

            array_shift($messages);
            $messages = array_values($messages);
            $messageParts = [];
            $trace = [];

            foreach ($messages as $i => $value) {
                if (preg_match('#^File "(.*)", line (\d+), in (.*)#', $value, $matches)) {
                    $trace[$i] = [
                        'file' => $matches[1],
                        'line' => (int) $matches[2],
                        'method' => $matches[3],
                        'statement' => $messages[$i + 1],
                    ];

                    continue;
                }

                if ($i > 0 && !isset($trace[$i - 1]) && preg_match('#\w+#', $messages[$i])) {
                    $messageParts[] = $value;
                }
            }

            $exception = new self(implode("\n", $messageParts), $faultCode);
            $exception->traceBack = array_reverse($trace);
        }

        return $exception;
    }

    public function getTraceBack(): array
    {
        return $this->traceBack;
    }
}
