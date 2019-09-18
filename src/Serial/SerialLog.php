<?php

namespace Serial;

class SerialLog
{
    protected $level;
    protected $message;
    protected $time;

    public function __construct($message, int $level = LOG_INFO)
    {
        $this->setLevel($level);
        $this->setMessage($message);
        $this->setTime(\microtime(true));
    }

    public function getLevel():string
    {
        return $this->level;
    }

    protected function setLevel(string $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    protected function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getTime():float
    {
        return $this->time;
    }

    protected function setTime(float $time): self
    {
        $this->time = $time;

        return $this;
    }

}
