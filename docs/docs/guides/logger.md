---
sidebar_position: 3
---

# Logger
You can use any implementation of [PSR-3 logger](https://www.php-fig.org/psr/psr-3/). This library provides a especial logger called `\Mateodioev\TgHandler\Log\Logger`

```php
use Mateodioev\TgHandler\Log\Logger;

$logger = new Logger($myStream);
// $myStream is an instance of Stream

// Adding to the bot
$bot = new Bot('TOKEN', $logger);
// or with a bot already created
$bot->setLogger($logger);
```
> See [Creating a new bot](../getting-started/creating-basic-bot)

## Writing logs

```php
$logger->debug('This is a log message');
// output: [Y-m-d H:i:s] [DEBUG] This is a debug message
```

Adding context
```php
$logger->info('This is a debug message with {paramName}', [
    'paramName' => 'context params'
    // note: sending "exception" key is treated different an will produce a different output
    'exception' => $e, // Throwable instance
]);
// output: [Y-m-d H:i:s] [INFO] This is a debug message with context params
```
## Stream
This is a resource where the logger will send the generated message

### Bulk stream
By default the logger instance only accepts one stream, to avoid this you can use the `Mateodioev\TgHandler\Log\BulkStream` and set all the streams you want to use

```php
$stream = new \Mateodioev\TgHandler\Log\BulkStream($stream1, $stream2, $stream3);
// Add more streams
$stream->add($anotherStream)->add($anotherAnotherStream);
```

### BotApiStream
This is a special stream the send the logs to a telegram chat.

```php
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Log\BotApiStream;

$botStream = new BotApiStream($apiInstance, 'CHAT ID/USERNAME');
```

Example with a bot instance already created

```php
$botStream = new BotApiStream($bot->getApi(), 'CHAT ID/USERNAME');
$bot->setLogger(new Logger($botStream));
```

Also you can set custom log levels to this stream (not affect the logger level). By default only critical, error and emergency levels are enable.

```php
$botStream->setLevel(Logger::DEBUG); // Add debug level
```

### FileStream
Write logs to a file

```php
$stream = new \Mateodioev\TgHandler\Log\FileStream($fileName);
// Also you can generate a file with the current date
$stream = \Mateodioev\TgHandler\Log\FileStream::fromToday($directory);
// Create a file like: /path/.../2024-01-01-php_error.log
```

### PhpNativeStream
Send all the errors to a file, also catch [php errors](https://www.php.net/manual/en/function.set-error-handler.php).

```php
$stream = new \Mateodioev\TgHandler\Log\PhpNativeStream();
$stream->activate('directory to save the file', 'file or null to generate one');
```

### TerminalStream
Output a log message to the terminal

```php
$stream = new \Mateodioev\TgHandler\Log\TerminalStream();
```

### ResourceStream
Send log messages to a [php resource](https://www.php.net/manual/en/language.types.resource.php)
. Used by `TerminalStream`

```php
$stream = new \Mateodioev\TgHandler\Log\ResourceStream(STDOUT);
```
### Creating your own stream
Create a new class an extend the Stream interface

```php
use Mateodioev\TgHandler\Log\Stream;
use SimpleLogger\streams\LogResult;

class MyCustomStream extends Stream
{
    public function push(LogResult $message, ?string $level): void
    {
        // Do whatever you want with the message
    }
}
```

**LogResult**
Is a object the contain the information about the current log message, properties:
- level: Level of the message, as a string
- message: The message set by the user
- exception: `\Throwable` instance. optional
- timestamp: unix-time when the message was generated

## Level
You can enable or disable certain log levels with the method `setLevel`, also you can use [bitwise flags](https://www.php.net/manual/en/language.operators.bitwise.php)

```php
$logger->setLevel(Logger::ALL); // Enable all the level. default
$logger->setLevel(~Logger::DEBUG | ~Logger::WARNING); // Disable debug and warning messages
$logger->setLevel(Logger::ALL, add: false); // Disable all the levels, you need to use this first before use certain log level
// Only enable certain levels
$logger->setLevel(Logger::DEBUG | Logger::NOTICE | Logger::CRITICAL | Logger::ERROR | Logger::EMERGENCY);
```

List of available levels:
- `Logger::ALl`
- `Logger::CRITICAL`
- `Logger::ERROR`
- `Logger::EMERGENCY`
- `Logger::ALERT`
- `Logger::WARNING`
- `Logger::NOTICE`
- `Logger::INFO`
- `Logger::DEBUG`

# How to access the logger
Logger is available in this context:
- [event execute](./events#creating-a-new-event)
- [context](./context)
    ```php
    // Example inside a middleware
    $ctx->logger->debug('123');
    ```