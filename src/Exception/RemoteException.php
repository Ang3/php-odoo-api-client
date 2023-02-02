<?php

declare(strict_types=1);

/*
 * This file is part of package ang3/php-odoo-api-client
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Ang3\Component\Odoo\Exception;

/**
 * @author Joanis ROUANET <https://github.com/Ang3>
 */
class RemoteException extends RequestException
{
    protected array $remoteTrace = [];

    public static function create(array $payload): self
    {
        $errorCode = $payload['error']['code'];
        $errorMessage = $payload['error']['message'];
        $remoteTrace = trim($payload['error']['data']['debug']);

        if (preg_match('#'.preg_quote('Traceback (most recent call last):').'#', $remoteTrace)) {
            $messages = array_filter(explode("\n", $remoteTrace));

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

            $exception = new self(implode("\n", $messageParts), $errorCode);
            $exception->remoteTrace = array_reverse($trace);

            return $exception;
        }

        return new self($errorMessage, $errorCode);
    }

    public function getRemoteTrace(): array
    {
        return $this->remoteTrace;
    }
}
