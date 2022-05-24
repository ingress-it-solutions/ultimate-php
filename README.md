# Palzin APM | Real-Time Code Execution monitoring and Bug tracking

[![Latest Stable Version](http://poser.pugx.org/ultimate-apm/ultimate-php/v)](https://packagist.org/packages/ultimate-apm/ultimate-php) [![Total Downloads](http://poser.pugx.org/ultimate-apm/ultimate-php/downloads)](https://packagist.org/packages/ultimate-apm/ultimate-php)  [![License](http://poser.pugx.org/ultimate-apm/ultimate-php/license)](https://packagist.org/packages/ultimate-apm/ultimate-php)

Simple code execution monitoring and bug reporting for PHP developers.

## Requirements

- PHP >= 7.2.0

## Install

Install the latest version of our package by:

```shell
composer require ultimate-apm/ultimate-php
```

## Use

To start sending data to Palzin APM you need an BUGTRAP Key to create an instance of the `Configuration` class.
You can obtain `ULTIMATE_BUGTRAP_KEY` creating a new project in your [Palzin APM](https://www.palzin.app) dashboard.

```php
use Ultimate\Ultimate;
use Ultimate\Configuration;

$configuration = new Configuration('YOUR_BUGTRAP_KEY');
$ultimate = new Ultimate($configuration);
```

All start with a `transaction`. Transaction represent an execution cycle and it can contains one or hundred of segments:

```php
// Start an execution cycle with a transaction
$ultimate->startTransaction($_SERVER['PATH_INFO']);
```

Use `addSegment` method to monitor a code block in your transaction:

```php
$result = $ultimate->addSegment(function ($segment) {
    // Do something here...
	return "Hello World!";
}, 'my-process');

echo $result; // this will print "Hello World!"
```

Palzin APM will monitor your code execution in real time and keep alerting you if something goes wrong.

## Custom Transport
You can also set up custom transport class to transfer monitoring data from your server to Palzin APM
in a personalized way.

The transport class needs to implement `\Ultimate\Transports\TransportInterface`:

```php
class CustomTransport implements \Ultimate\Transports\TransportInterface
{
    protected $configuration;

    protected $queue = [];

    public function __constructor($configuration)
    {
        $this->configuration = $configuration;
    }

    public function addEntry(\Ultimate\Models\Arrayable $entry)
    {
        // Add an \Ultimate\Models\Arrayable entry in the queue.
        $this->queue[] = $entry;
    }

    public function flush()
    {
        // Performs data transfer.
        $handle = curl_init('https://www.palzin.app');
        curl_setopt($handle, CURLOPT_POST, 1);
        curl_setopt($handle, CURLOPT_HTTPHEADER, [
            'X-Ultimate-Key: xxxxxxxxxxxx',
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($this->queue));
        curl_exec($handle);
        curl_close($handle);
    }
}
```

Then you can set the new transport in the `Ultimate` instance
using a callback the will receive the current configuration state as parameter.

```php
$ultimate->setTransport(function ($configuration) {
    return new CustomTransport($configuration);
});
```

## LICENSE

This package is licensed under the [MIT](LICENSE) license.
