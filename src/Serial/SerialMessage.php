<?php

namespace Serial;

class SerialMessage
{
    protected $content = null;
    protected $waitForReply = 0.1;
    protected $callback = null;

    public function __construct($content)
    {
        $this->setContent($content);
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getWaitForReply(): float
    {
        return $this->waitForReply;
    }

    public function setWaitForReply(float $waitForReply): self
    {
        $this->waitForReply = $waitForReply;

        return $this;
    }

    public function getCallback(): ?callable
    {
        return $this->callback;
    }

    public function setCallback(callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }
}
