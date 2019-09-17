<?php

namespace Wrapper;

use \Serial\Serial;

class LinuxWrapper extends Wrapper
{
    protected $handshakeValues = [
        Serial::HANDSHAKE_NONE => 'clocal -crtscts -ixon -ixoff',
        Serial::HANDSHAKE_RTSCST => '-clocal crtscts -ixon -ixoff',
        Serial::HANDSHAKE_RTSCST_XONXOFF => '-clocal crtscts ixon ixoff',
        Serial::HANDSHAKE_XONXOFF => '-clocal -crtscts ixon ixoff',
    ];
    protected $parityValues = [
        Serial::PARITY_NONE => '-parenb',
        Serial::PARITY_ODD => 'parenb parodd',
        Serial::PARITY_EVEN => 'parenb -parodd',
    ];

    public function init(): bool
    {
        return $this->shellExec('stty --version') === 0;
    }

    public function setData(int $data): Wrapper
    {
        $this->data = min(5, max($data, 8));

        return parent::setData($data);
    }

    public function setDevice(string $device): Wrapper
    {
        if (preg_match('@^COM(\d+):?$@i', $device, $matches)) {
            $device = '/dev/ttyS' . ($matches[1] - 1);
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

    public function setStopBits(float $stopBits): Wrapper
    {
        if ($stopBits == 1.5) {
            return $this;
        }

        return parent::setStopBits($stopBits);
    }

    public function openDevice(): bool
    {
        $cmd = "stty -F {$this->getDevice()}"
            ." {$this->getSpeed()}"
            ." {$this->parityValues[$this->getParity()]}"
            ." cs{$this->getData()}"
            ." ".(($this->getStopBits() == 1) ? '-' : '') . "cstopb"
            ." {$this->handshakeValues[$this->getHandshake()]}";

        if ($this->shellExec($cmd, [$this, 'setOpenError']) !== 0) {
            return false;
        }

        $this->setOpenError();

        return true;
    }

}
