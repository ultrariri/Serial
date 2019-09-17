<?php

namespace Wrapper;

use \Serial\Serial;

abstract class Wrapper implements IWrapper
{
    private $device = null;
    private $data = 8;
    private $handshake = Serial::HANDSHAKE_NONE;
    private $mode = Serial::MODE_READWRITE;
    private $openError = null;
    private $parity = Serial::PARITY_NONE;
    private $speed = 9600;
    private $stopBits = 1;

    protected function shellExec(string $cmd, callable $callback = null): int
    {
        $descriptorSpec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptorSpec, $pipes);
        $stdOut = stream_get_contents($pipes[1]);
        $stdErr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        if (\is_callable($callback)) {
            \call_user_func($callback, $stdOut, $stdErr);
        }

        return $returnCode;
    }

    protected function setOpenError(string $stdOut = null, string $stdErr = null): self
    {
        $this->openError = $stdErr;

        return $this;
    }

    public function getOpenError(): string
    {
        return (string)$this->openError;
    }

    public function __construct() { }

    public function getData(): int
    {
        return $this->data;
    }

    public function setData(int $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getDevice(): string
    {
        return $this->device;
    }

    public function setDevice(string $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function getHandshake(): string
    {
        return $this->handshake;
    }

    public function setHandshake(string $handshake): self
    {
        $this->handshake = $handshake;

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getParity(): string
    {
        return $this->parity;
    }

    public function setParity(string $parity): self
    {
        $this->parity = $parity;

        return $this;
    }

    public function getSpeed(): int
    {
        return $this->speed;
    }

    public function setSpeed(int $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getStopBits(): float
    {
        return $this->stopBits;
    }

    public function setStopBits(float $stopBits): self
    {
        $this->stopBits = $stopBits;

        return $this;
    }
}
