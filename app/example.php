<?php

require_once __DIR__.'/../Bootstrap.php';

use \Serial\Serial;
use \Serial\SerialException;
use \Serial\SerialLog;
use \Serial\SerialMessage;

class SerialExample
{
    public $serial;

    public function __construct()
    {
        try {
            $this->serial = (new Serial('/dev/ttyUSB0', '9600', '8/N/1'))
                ->setVerboseCallback([$this, 'cbLogs']);

            $this->sendHelloWorld();
            $this->sendThisFile();

        } catch (SerialException $except) {
            echo $except->getMessage();
        }
    }

    public function sendHelloWorld()
    {
        $message = (new SerialMessage('Hello World!'))
            ->setCallback([$this, 'cbSendHelloWorld'], 1000);

        $this->serial->write($message);
    }

    public function sendThisFile()
    {
        $message = (new SerialMessage(file_get_contents(__FILE__)))
            ->setCallback([$this, 'cbSendThisFile']);

        $this->serial->write($message);
    }

    public function cbLogs(SerialLog $log)
    {
        printf("\t%s\t\t%s%s", $log->getLevelName(), $log->getMessage(), PHP_EOL);

    }

    public function cbSendHelloWorld()
    {
        echo 'Hello World sent...'.PHP_EOL;
    }

    public function cbSendThisFile()
    {
        echo 'File sent...'.PHP_EOL;
    }

}

try {
    $example = new SerialExample;
} catch (SerialException $se) {
    echo $se->getMessage();
}
