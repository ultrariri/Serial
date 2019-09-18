<?php

require_once __DIR__.'/../Bootstrap.php';

use \Serial\Serial;
use \Serial\SerialException;
use \Serial\SerialLog;
use \Serial\SerialMessage;

class SerialExample
{
    protected $serial;

    public function __construct()
    {
        try {
            $this->serial = (new Serial('/dev/ttyUSB0', '4800', '8/N/1'))
                ->setVerboseCallback([$this, 'logs']);

            $this->sendHelloWorld();
            $this->sendThisFile();

        } catch (SerialException $except) {
            echo $except->getMessage();
        }
    }

    public function sendHelloWorld()
    {
        $message = (new SerialMessage('Hello Worl!'))
            ->setCallback([$this, 'HWSent'])
            ->setWaitForReply(2000);

        $this->getSerial()->write($message);
    }

    public function sendThisFile()
    {
        $message = (new SerialMessage(file_get_contents(__FILE__)))
            ->setCallback([$this, 'FileSent'])
            ->setWaitForReply(2000);

        $this->getSerial()->write($message);
    }

    public function getSerial(): Serial
    {
        return $this->serial;
    }

    public function HWSent()
    {
        echo 'Hello World sent...'.PHP_EOL;
    }

    public function FileSent()
    {
        echo 'File sent...'.PHP_EOL;
    }

    public function logs(SerialLog $log)
    {
        printf("\t%s\t\t%s%s", $log->getLevelName(), $log->getMessage(), PHP_EOL);

    }

}

try {
    $example = new SerialExample;
} catch (SerialException $se) {
    echo $se->getMessage();
}
