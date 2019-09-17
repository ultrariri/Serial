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
        $message = (new SerialMessage('Hello World!'))
            ->setCallback([$this, 'HWSent'])
            ->setWaitForReply(0.1);

        try {
            $this->serial = (new Serial('/dev/ttyS0', '9600', '8/N/1'))
                ->openDevice()
                ->write($message);

            $answer = $this->serial->read();

            $this->serial->closeDevice();
        } catch (SerialException $except) {
            echo $except->getMessage();
        }
    }

    public function HWSent()
    {
        echo 'Hello World sent...';
    }

}

$example = new SerialExample;
