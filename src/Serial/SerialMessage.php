<?php

namespace Serial;

class SerialMessage
{
    protected $callback = null;
    protected $content = null;
    protected $synchronous = true;
    protected $waitForCallback = 0;

    public function __construct($content)
    {
        $this->setContent($content);
    }

    /**
     * Get the function called while writing to the device
     *
     * @return callable|null
     */
    public function getCallback(): ?callable
    {
        return $this->callback;
    }

    /**
     * Set the function called while writing to the device
     *
     * @param callable $callback Function
     * @param integer $waitForCallback Wait before call the function
     * @return self
     */
    public function setCallback(callable $callback, int $waitForCallback = 0): self
    {
        $this->callback = $callback;
        $this->setWaitForCallback($waitForCallback);

        return $this;
    }

    /**
     * Get the message content
     *
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Set the message content
     *
     * @param string $content Content
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Synchronous or asynchrnous callback
     *
     * @return boolean
     */
    public function isSynchronous(): bool
    {
        return $this->synchronous;
    }

    /**
     * Callback after the message is totaly sent
     *
     * @return boolean
     */
    public function setSynchronous(): self
    {
        $this->synchronous = true;

        return $this;
    }

    /**
     * Callback before the message is totaly sent
     *
     * @return boolean
     */
    public function setAsynchronous(): self
    {
        $this->synchronous = false;

        return $this;
    }

    /**
     * Time to wait before callback
     *
     * @return float
     */
    public function getWaitForCallback(): float
    {
        return $this->waitForCallback;
    }

    /**
     * Set the time to wait before callback
     *
     * @param float $waitForCallback Time is ms
     * @return self
     */
    protected function setWaitForCallback(float $waitForCallback = 0): self
    {
        $this->waitForCallback = $waitForCallback;

        return $this;
    }

}
