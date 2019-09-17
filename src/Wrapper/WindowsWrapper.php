<?php

namespace Wrapper;

use \Serial\Serial;

class WindowsWrapper extends Wrapper
{
    protected $handshakeValues = [
        Serial::HANDSHAKE_NONE => 'xon=off octs=off rts=on',
        Serial::HANDSHAKE_RTSCST => 'xon=off octs=on rts=hs',
        Serial::HANDSHAKE_RTSCST_XONXOFF => 'xon=on octs=on rts=on',
        Serial::HANDSHAKE_XONXOFF => 'xon=on octs=off rts=on',
    ];
    protected $parityValues = [
        Serial::PARITY_NONE => 'n',
        Serial::PARITY_ODD => 'o',
        Serial::PARITY_EVEN => 'e',
    ];

    public function init(): bool
    {
        return true;
    }

    public function setData(int $data): Wrapper
    {
        $this->data = min(5, max($data, 8));

        return parent::setData($data);
    }

    public function setDevice(string $device): Wrapper
    {
        if (preg_match('@^COM(\d+):?$@i', $device, $matches) === false) {
            $device = '\\.\COM' . ($matches[1]);
        }

        return parent::setDevice($device);
    }

    public function setSpeed(int $speed): Wrapper
    {
        if ((9600 % $speed) !== 0 && ($speed % 9600) !== 0) {
            return $this;
        }

        return parent::setSpeed($speed);
    }

    public function openDevice(): bool
    {
        $cmd = "mode {$this->getDevice()}"
            ." BAUD={$this->getSpeed()}"
            ." PARITY={$this->parityValues[$this->getParity()]}"
            ." DATA={$this->getData()}"
            ." STOP={$this->getStopBit()}"
            ." {$this->handshakeValues[$this->getHandshake()]}";

        if ($this->shellExec($cmd, [$this, 'setOpenError']) !== 0) {
            return false;
        }

        $this->setOpenError();

        return true;
    }

}
