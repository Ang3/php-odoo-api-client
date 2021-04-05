<?php

namespace Ang3\Component\Odoo\Exception;

use Ang3\Component\XmlRpc\Exception\RemoteException as XmlRemoteException;

class RemoteException extends RequestException
{
    /**
     * @var array
     */
    protected $xmlTrace = [];

    public static function create(XmlRemoteException $remoteException): self
    {
        $errorCode = $remoteException->getCode();
        $errorMessage = $remoteException->getMessage();

        if (preg_match('#Traceback \(most recent call last\)#', $errorMessage)) {
            $messages = array_filter(explode("\n", $errorMessage));

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
            $exception->xmlTrace = array_reverse($trace);

            return $exception;
        }

        return new self($errorMessage, $errorCode);
    }

    public function getXmlTrace(): array
    {
        return $this->xmlTrace;
    }
}
