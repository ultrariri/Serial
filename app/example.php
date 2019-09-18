<?php

require_once __DIR__.'/../Bootstrap.php';

use \Serial\Serial;
use \Serial\SerialException;
use \Serial\SerialMessage;

class SerialExample
{
    protected $serial;

    public function __construct()
    {
        $message = (new SerialMessage(file_get_contents(__FILE__)))
            ->setCallback([$this, 'HWSent'])
            ->setWaitForReply(2000);

        try {
            $this->serial = (new Serial('/dev/ttyUSB0', '9600', '8/N/1'))
                ->openDevice()
                ->write($message);

            $answer = $this->serial->read(256);
        } catch (SerialException $except) {
            echo $except->getMessage();
        }
    }

    public function getSerial(): Serial
    {
        return $this->serial;
    }

    public function HWSent()
    {
        echo 'Hello World sent...';
    }

}

try {
    $example = new SerialExample;

    foreach ($example->getSerial()->getLogs() as $message) {
        printf("%s\t\t%s%s", $message->getTime(), $message->getMessage(), PHP_EOL);
    }
} catch (SerialException $se) {
    echo $se->getMessage();
} catch (Error $e) {
    echo $e->getMessage();
}
