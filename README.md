# Telegram bot handler

## Installation

```bash
composer require mateodioev/tg-handler
```

```php
use Mateodioev\TgHandler\Bot;
```

## Usage

Create new instance and add your command (*for now only text commands is available*)

```php
$bot = new Bot($botToken);

$bot->onEvent(YourCommandInstance);

$bot->byWebhook();
// or
$bot->longPolling($timeout); // No needs server with ssl
```

### Creating new command instance

All text commands need to extend `Mateodioev\TgHandler\Commands\MessageCommand` class, and put the logic in the method `handle`.

```php
use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

class MyCommand extends MessageCommand
{
    protected string $name = 'start';
    protected string $description = 'Start the bot';
    
    public function handle(Api $bot, Context $context){
         // TODO: Implement handle() method.
         $bot->sendMessage($context->getChatId(), 'Hello!!!');
    }
}

$bot->onEvent(MyCommand::get());
```

This command works with `/start any text here` or `/start`. If you need another prefixes (/, !) you can add with method `addPrefix` or with property `prefix`

```php
class MyCommand extends MessageCommand
{
    protected string $name = 'start';
    protected string $description = 'Start the bot';
    protected array $prefix = ['/', '!'];
    
    // Additionally you can add command aliases
    protected array $alias = ['help'];
    
    ...
}

// or
$myCommand = MyCommand::get();
$myCommand->setPrefixes(['/', '!']); // Set prefix, no need to set `$prefix` property
```

### Using middlewares

You can add middlewares to your command. Middlewares are closures that will be executed before the command. All middlewares results will be passed to the command as an array.

For example, you can create a middleware that will check if the user is authorized, and if not, the command will not be executed.

```php
class MyCommand extends MessageCommand
{
    protected string $name = 'start';
    protected string $description = 'Start the bot';
    protected array $middlewares = [
        'authUser'
    ];
    
    public function handle(Api $bot, Context $context, array $args = []){
        // $args[0] is the result of the middleware authUser
        // Your logic here
    }
}

// Your middleware function 
function authUser(Context $ctx, Api $bot) {
    $user = User::find($ctx->getUserId());
    if (!$user) {
        $bot->replyTo($ctx->getChatId(), 'You are not authorized', $ctx->getMessageId())
        throw new \Mateodioev\TgHandler\Commands\StopCommand(); // Stop command execution
    }
    return $user;
}
```
> You can use `StopCommand` exception to stop command execution

### Loging

#### Basic usage

```php
use Mateodioev\TgHandler\Log\{Logger, TerminalStream};

$log = new Logger(new TerminalStream); // Print logs in terminal
$bot->setLogger($log); // Save logger
```

#### Collections of streams

```php
use Mateodioev\TgHandler\Log\{BulkStream, Logger, TerminalStream, PhpNativeStream};

BulkStream::add(new TerminalStream); // print logs in terminal
BulkStream::add((new PhpNativeStream)->activate(__DIR__)); // save logs in .log file and catch php warnings

$bot->setLogger(new Logger(new BulkStream));
```

#### Set log level

```php
use Mateodioev\TgHandler\Log\{Logger, TerminalStream};

$log = new Logger(new TerminalStream);

// disable all logs
$log->setLevel(Logger:ALL, false);
// Enable only critical, error and emergency messages
$log->setLevel(Logger::CRITICAL | Logger::ERROR | Logger::EMERGENCY);

$bot->setLogger($log);
```

#### Use

The logger can be used from the bot or event instances (MessageCommand, CallbackCommand, etc)

_Bot:_
```php
$bot->getLogger()->debug('This is a debug message');
// output: [Y-m-d H:i:s] [DEBUG] This is a debug message
$bot->getLogger()->info('This is a debug message with {paramName}', [
    'paramName' => 'context params'
]);
// output: [Y-m-d H:i:s] [INFO] This is a debug message with context params
```

_Event instances:_

```php
use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

class MyCommand extends MessageCommand
{
    protected string $name = 'start';
    protected string $description = 'Start the bot';
    
    public function handle(Api $bot, Context $context)
    {
         $this->logger()->debug('Loging inside event');
         $this->logger()->info('User {name} use the command {commandName}', [
            'name'        => $context->getUserName() ?? 'null',
            'commandName' => $this->name
         ]);
    }
}

// register the command
$bot->onEvent(MyCommand::get());
```