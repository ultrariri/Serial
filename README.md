# Serial

## Send and read message throught a serial port

Example of a simple "Hello World!" message, with a callback, sent througt /dev/ttyS0 (COM1), at 9600 bauds, 8 bits, no parity, 1 stop bit.

The callback function is called if the ```Serial::write()``` method is successful.

```
class SerialExample
{
    public $serial;

    public function __construct()
    {
        try {
            $this->serial = (new Serial('/dev/ttyUSB0', '9600', '8/N/1'))
                ->setVerboseCallback([$this, 'cbLogs']);

            $this->sendThisFile();

        } catch (SerialException $except) {
            echo $except->getMessage();
        }
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
```

## Serial options
 - Device
 - Speed (_Bauds_)
 - Data bits
 - Parity mode
 - Stop bits
 - Handshake mode
 - Serial port access (R/W)

## Message option
 - Content
 - Callback function
   - Synchronous
   - Wait
