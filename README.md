# Serial

## Send and read message throught a serial port

Example of a simple "Hello World!" message, with a callback, sent througt /dev/ttyS0 (COM1), at 9600 bauds, 8 bits, no parity, 1 stop bit.

The callback function is called if the ```Serial::write()``` method is successful.

```
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
```

## Serial options
 - Device
 - Speed (Bauds)
 - Data bits
 - Parity mode
 - Stop bits
 - Handshake mode
 - Serial port access (R/W)

