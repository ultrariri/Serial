<?php

namespace Serial;

use \Wrapper\Wrapper;

class Serial extends SerialException
{
    const DEVICE_NOTSET = -1;
    const DEVICE_CLOSED = 0;
    const DEVICE_SET = 1;
    const DEVICE_OPENED = 2;
    const HANDSHAKE_NONE = 0;
    const HANDSHAKE_RTSCST = 1;
    const HANDSHAKE_RTSCST_XONXOFF = 2;
    const HANDSHAKE_XONXOFF = 3;
    const MODE_READ = 'rb';
    const MODE_READWRITE = 'r+b';
    const MODE_WRITE = 'ab';
    const PARITY_NONE = 'N';
    const PARITY_ODD = 'O';
    const PARITY_EVEN = 'E';

    protected $autoflush = true;
    protected $buffer = '';
    protected $logs;
    protected $readLength = 128;
    protected $deviceHandle = null;
    protected $deviceState = self::DEVICE_NOTSET;
    protected $verboseCallback = null;
    protected $wrapper = null;

    /**
     * Constructor
     *
     * @param string $device    Device URI
     * @param string $bps       DataBits/Parity/StopBits
     */
    public function __construct(string $device = null, int $speed = 9600, string $bps = '8/N/1')
    {
        $this->logs = new \SplObjectStorage;

        $this->registerWrapper();

        !is_null($device) and $this->setDevice($device);
        !is_null($device) and $this->setSpeed($speed);
        !is_null($device) and $this->setDataParityStop($bps);
    }

    protected function registerWrapper(): self
    {
        $phpOs = strtolower(PHP_OS);

        switch (true) {
            case $phpOs === 'darwin':
            case $phpOs === 'freebsd':
            case $phpOs === 'netbsd':
            case $phpOs === 'openbsd':
                $this->setWrapper(new \Wrapper\DarwinWrapper());
                break;
            case $phpOs === 'linux':
                $this->setWrapper(new \Wrapper\LinuxWrapper());
                break;
            case substr($phpOs, 0, 6) === 'cygwin':
            case $phpOs === 'win32':
            case $phpOs === 'windows':
            case $phpOs === 'winnt':
                $this->setWrapper(new \Wrapper\WindowsWrapper());
                break;
            default:
                throw new SerialException('Unknown platform '.PHP_OS.', unable to run.');
        }

        return $this;
    }

    protected function isDeviceNotSet(): bool
    {
        return $this->deviceState === self::DEVICE_NOTSET;
    }

    protected function isDeviceSet(): bool
    {
        return $this->deviceState !== self::DEVICE_NOTSET;
    }

    protected function isDeviceOpened(): bool
    {
        return $this->deviceState === self::DEVICE_OPENED;
    }

    protected function isDeviceClosed(): bool
    {
        return $this->deviceState === self::DEVICE_CLOSED;
    }

    protected function canRead(): bool
    {
        return $this->getWrapper()->getMode() === self::MODE_READ || $this->getWrapper()->getMode() === self::MODE_READWRITE;
    }

    protected function canWrite(): bool
    {
        return $this->getWrapper()->getMode() === self::MODE_WRITE || $this->getWrapper()->getMode() === self::MODE_READWRITE;
    }

    protected function addLog(SerialLog $log)
    {
        $this->logs->attach($log);

        if (\is_callable($this->getVerboseCallback())) {
            $this->getVerboseCallback()($log);
        }
    }

    /**
     * Get logs
     *
     * @return \SplObjectStorage
     */
    public function getLogs(): \SplObjectStorage
    {
        return $this->logs;
    }

    /**
     * Get last Nth logs
     *
     * @param integer $nth Number of log objects
     * @return \SplObjectStorage
     */
    public function getNthLogs(int $nth): \SplObjectStorage
    {
        $count = $this->logs->count();

        if ($nth >= $count) {
            return $this->logs;
        }

        $start = $count - $nth;
        $returnLogs = new \SplObjectStorage;
        $clonedLogs = clone $this->logs;

        $clonedLogs->rewind();

        do {
            if ($clonedLogs->key() >= $start) {
                $returnLogs->attach($clonedLogs->current());
            }

            $clonedLogs->next();
        } while ($clonedLogs->valid());

        unset($clonedLogs);

        return $returnLogs;
    }

    /**
     * Get function to send log to
     *
     * @param callable $callback Function
     * @return callable|null
     */
    public function getVerboseCallback(callable $callback = null): ?callable
    {
        return $this->verboseCallback;
    }

    /**
     * Set function to send log to
     *
     * @param callable $callback Function
     * @return callable|null
     */
    public function setVerboseCallback(callable $callback = null): self
    {
        $this->verboseCallback = $callback;

        return $this;
    }

    /**
     * Set Data, Parity and Stop with "D/P/S" format
     *
     * @param string $bps "D/P/S" format
     * @return self
     */
    public function setDataParityStop($bps = '8/N/1'): self
    {
        if (preg_match('#^(\d+)/(\w{1})/([\d\.]+)$#', $bps, $matches) === 1) {
            $this->wrapper->setData($matches[1]);
            $this->wrapper->setParity($matches[2]);
            $this->wrapper->setStopBits($matches[3]);
        }

        return $this;
    }

    protected function getDeviceHandle()
    {
        return $this->deviceHandle;
    }

    protected function setDeviceHandle($deviceHandle = null)
    {
        $this->deviceHandle = $deviceHandle;

        return $this;
    }

    /**
     * Get autoflush status
     *
     * @return boolean
     */
    public function getAutoflush(): bool
    {
        return $this->autoflush;
    }

    /**
     * Enable autoflush
     *
     * @return boolean
     */
    public function setAutoflush(): self
    {
        $this->autoflush = true;

        return $this;
    }

    /**
     * Disable autoflush
     *
     * @return boolean
     */
    public function unsetAutoflush(): self
    {
        $this->autoflush = false;

        return $this;
    }

    /**
     * Set data length
     *
     * @param int $data Length of a character
     * @return self
     */
    public function setData(int $data): self
    {
        if ($this->isDeviceOpened()) {
            throw new SerialException('Device already opened, unable to change data length.');
        }

        $this->getWrapper()->setData($data);

        return $this;
    }

    /**
     * Set serial device
     * Examples:
     * - darwin: /dev/tty.serial
     * - linux: /dev/ttyS0
     * - windows: COM1
     *
     * @param string $device Device URI or name
     * @return self
     */
    public function setDevice(string $device): self
    {
        if ($this->isDeviceOpened()) {
            throw new SerialException('Device already opened, unable to change device name.');
        }

        $this->getWrapper()->setDevice($device);

        $this->deviceState = self::DEVICE_SET;

        return $this;
    }

    /**
     * Set handshake
     * - see self::HANDSHAKE_* for values
     *
     * @param string $handshake Handshake mode
     * @return self
     */
    public function setHandshake(string $handshake): self
    {
        if ($this->isDeviceOpened()) {
            throw new SerialException('Device already opened, unable to change handshake.');
        }

        if (!in_array($handshake, [
            self::HANDSHAKE_NONE,
            self::HANDSHAKE_RTSCST,
            self::HANDSHAKE_XONXOFF,
            ])) {
            throw new SerialException('Invalid flow control.');
        }

        $this->getWrapper()->setHandshake($handshake);

        return $this;
    }

    /**
     * Set access mode to serial port
     * - see self::MODE_* for values
     *
     * @param string $mode Access mode
     * @return self
     */
    public function setMode(string $mode): self
    {
        if ($this->isDeviceOpened()) {
            throw new SerialException('Device already opened, unable to change access mode.');
        }

        if (!in_array($mode, [
            self::MODE_READ,
            self::MODE_READWRITE,
            self::MODE_WRITE,
            ])) {
            throw new SerialException('Invalid mode.');
        }

        $this->getWrapper()->setMode($mode);

        return $this;
    }

    /**
     * Set parity
     * - see self::PARITY_* for values
     *
     * @param string $parity Parity mode
     * @return self
     */
    public function setParity(string $parity): self
    {
        if ($this->isDeviceOpened()) {
            throw new SerialException('Device already opened, unable to change parity.');
        }

        if (!in_array($parity, [
            self::PARITY_EVEN,
            self::PARITY_NONE,
            self::PARITY_ODD,
            ])) {
            throw new SerialException('Invalid parity mode.');
        }

        $this->getWrapper()->setParity($parity);

        return $this;
    }

    /**
     * Set speed (Baud rate)
     *
     * @param int $speed The speed in bauds
     * @return self
     */
    public function setSpeed(int $speed): self
    {
        if ($this->isDeviceOpened()) {
            throw new SerialException('Device already opened, unable to change speed.');
        }

        $this->getWrapper()->setSpeed($speed);

        return $this;
    }

    /**
     * Set stop bits
     * - usually 1, 1.5 or 2
     *
     * @param float $stopBits Length of a stop bit
     * @return bool
     */
    public function setStopBits(float $stopBits): self
    {
        if ($this->isDeviceOpened()) {
            throw new SerialException('Device already opened, unable to change stop bits.');
        }

        if (!in_array($stopBits, [
            1,
            1.5,
            2,
            ])) {
            throw new SerialException('Stop bit length is not valid.');
        }

        $this->getWrapper()->setStopBits($stopBits);
        return $this;
    }

    protected function getReadLength(): int
    {
        return $this->readLength;
    }

    /**
     * Set buffer read length
     *
     * @param integer $readLength Length
     * @return void
     */
    public function setReadLength(int $readLength = 128)
    {
        $this->readLength = $readLength;

        return $this;
    }

    protected function setWrapper(Wrapper $wrapper): self
    {
        $this->wrapper = $wrapper;

        return $this;
    }

    /**
     * Get the platform wrapper
     *
     * @return Wrapper|null
     */
    public function getWrapper(): ?Wrapper
    {
        return $this->wrapper;
    }

    protected function microtimeDiff(float $from): string
    {
        return round(\microtime(true)-$from, 2);
    }

    protected function flushBuffer(): bool
    {
        $this->addLog(new SerialLog('Flushing buffer...'));

        $this->openDevice();

        if (!$this->canWrite()) {
            throw new SerialException('Device can\'t be written: not allowed.');
        }

        if (@fwrite($this->getDeviceHandle(), $this->buffer) === false) {
            throw new SerialException('Error while sending message.');
        }

        $this->buffer = '';

        return true;
    }

    /**
     * Open device for communication
     *
     * @return self
     */
    public function openDevice(): self
    {
        $this->addLog(new SerialLog('Opening device...'));

        if ($this->isDeviceNotSet()) {
            throw new SerialException('Device can\t be opened: not set.');
        }

        if ($this->isDeviceOpened()) {
            throw new SerialException('Device already opened.');
        }

        if (!\is_subclass_of($this->getWrapper(), 'Wrapper\Wrapper', false)) {
            throw new SerialException('Wrapper not set.');
        }

        if (!$this->getWrapper()->init()) {
            throw new SerialException('Wrapper initialisation failed.');
        }

        $this->getWrapper()->openDevice();

        $this->setDeviceHandle(@fopen($this->getWrapper()->getDevice(), $this->getWrapper()->getMode()));

        if ($this->getDeviceHandle() === false) {
            $this->addLog(new SerialLog($this->wrapper->getOpenError(), LOG_ERR));
            $this->setDeviceHandle();

            throw new SerialException('Unable to open the device: '.$this->wrapper->getOpenError());
        }

        register_shutdown_function([$this, 'closeDevice']);

        $this->deviceState = self::DEVICE_OPENED;

        $this->addLog(new SerialLog('Device opened.'));

        return $this;
    }

    /**
     * Close device communication
     *
     * @return self
     */
    public function closeDevice(): bool
    {
        if ($this->isDeviceClosed()) {
            return true;
        }

        $this->addLog(new SerialLog('Wait for freeing device...'));

        if (@fclose($this->getDeviceHandle()) === false) {
            return false;
        }

        $this->deviceState = self::DEVICE_CLOSED;

        $this->addLog(new SerialLog('Device closed.'));

        return true;
    }

    /**
     * Read port content
     *
     * @param int $length Length
     * @return string
     */
    public function read($length = 0): string
    {
        $start = \microtime(true);

        $this->addLog(new SerialLog("Reading {$length} bytes..."));

        $this->openDevice();

        if ($this->isDeviceClosed()) {
            throw new SerialException('Device is closed.');
        }

        if (!$this->isDeviceOpened()) {
            throw new SerialException('Device can\'t be read: not opened.');
        }

        if (!$this->canRead()) {
            throw new SerialException('Device can\'t be read: not allowed.');
        }

        $content = '';
        $readLength = $this->getReadLength();
        $loop = 0;

        do {
            $this->addLog(new SerialLog("Read loop {$loop}..."));

            if ($length !== 0) {
                $readLength = ($loop > $length)
                    ? ($length - $loop)
                    : $this->getReadLength();
            }

            $content.= fread($this->getDeviceHandle(), $readLength);
        } while (($loop += $this->getReadLength()) === strlen($content));

        if ($this->closeDevice()) {
            $this->addLog(new SerialLog("Read in ".$this->microtimeDiff($start).'s.'));
        }

        return $content;
    }

    /**
     * Sends a message to the device
     *
     * @param SerialMessage $message Message
     * @return self
     */
    public function write(SerialMessage $message): self
    {
        $start = \microtime(true);

        $this->addLog(new SerialLog('Writing...'));

        if ($this->isDeviceNotSet()) {
            throw new SerialException('Device can\t be written: not set.');
        }

        $this->buffer.= $message->getContent();

        if ($this->autoflush === true) {
            $this->flushBuffer();

            if (!$message->isSynchronous()) {
                $this->writeCallback($message);
            }

            $this->closeDevice();
            $this->addLog(new SerialLog("Sent in ".$this->microtimeDiff($start).'s'));

            if ($message->isSynchronous()) {
                $this->writeCallback($message);
            }
        }

        $this->closeDevice();

        return $this;
    }

    protected function writeCallback(SerialMessage $message)
    {
        if (!\is_callable($message->getCallback())) {
            return true;
        }

        if ($message->getWaitForCallback() > 0) {
            $this->addLog(new SerialLog('Wait before write callback '.round($message->getWaitForCallback()/1000, 2).'s...'));

            usleep(intval(($message->getWaitForCallback() * 1000)));
        }

        $this->addLog(new SerialLog('Calling write callback function: '.$message->getCallback()[1].'()'));

        \call_user_func($message->getCallback());
    }

}
