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

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getLevelName(): string
    {
        switch ($this->getLevel()) {
            case LOG_EMERG: return 'EMERG';
            case LOG_ALERT: return 'ALERT';
            case LOG_CRIT: return 'CRIT';
            case LOG_ERR: return 'ERR';
            case LOG_WARNING: return 'WARNING';
            case LOG_NOTICE: return 'NOTICE';
            case LOG_INFO: return 'INFO';
            case LOG_DEBUG: return 'DEBUG';
            default: return '';
        }
    }

    protected function setLevel(int $level): self
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

    public function getTime(): float
    {
        return $this->time;
    }

    protected function setTime(float $time): self
    {
        $this->time = $time;

        return $this;
    }

}
