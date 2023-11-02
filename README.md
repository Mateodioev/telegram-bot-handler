# Telegram bot handler

## Installation

```bash
composer require mateodioev/tg-handler:"~5.1"
```

```php
use Mateodioev\TgHandler\Bot;
```

## Usage

Create new instance and add your command

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
    
    public function execute(array $args = []){
         $this->api()->sendMessage($this->ctx()->getChatId(), 'Hello!!!');
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
    
    public function execute(array $args = []){
        // $args[0] is the result of the middleware authUser
        // Your logic here ...
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

### Using filters

Now you can set custom filters to you event for validate, all filters need to extends the interface `Mateodioev\TgHandler\Filters\Filter`

```php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

[\Mateodioev\TgHandler\Filters\FilterFromUserId(996202950)];
class FilterCommand extends MessageCommand
{
    protected string $name = 'filter';
    
    public function handle(Api $bot, Context $context, array $args = [])
    {
    }
}
```

Now this command only respond to the user ID `996202950`

#### Using multiple filters

You can use `FilterCollection` 

```php
use Mateodioev\TgHandler\Events\Types\MessageEvent;
use Mateodioev\TgHandler\Filters\{FilterCollection, FilterMessageChat, FilterMessageRegex};

#[FilterCollection(
    new FilterMessageChat(TestChat::CHAT_ID),
    new FilterMessageRegex('/.*(mt?proto).*/i')
)]
class TestChat extends MessageEvent {
    const CHAT_ID = 'Put your chat id here';
    public function execute(Api $bot, Context $context, array $args = []) {
        // your logic here
    }
}
```

Or can use this syntax
```php
use Mateodioev\TgHandler\Events\Types\MessageEvent;
use Mateodioev\TgHandler\Filters\{FilterCollection, FilterMessageChat, FilterMessageRegex};

#[FilterMessageChat(TestChat::CHAT_ID), FilterMessageRegex('/.*(filters).*/i')]
class TestChat extends MessageEvent {
    const CHAT_ID = 'Put your chat id here';
    public function execute(array $args = []) {
        // your logic here
    }
}
```

### Conversations

To start a new conversation you simply return an instance of the `Mateodioev\TgHandler\Conversations\Conversations` in a interface of `Mateodioev\TgHandler\Events\EventInterface`

**Conversation:**

```php
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\MessageConversation;

class MyConversation extends MessageConversation
{
    // This is optional, only for validate the user input message
    protected string $format = 'My name is {w:name}';
    public function execute(array $args = [])
    {
        $this->api()->sendMessage(
            $this->ctx()->getChatId(),
            'Nice to meet you ' . $this->param('name') // Get param defined in $format, if not defined return null
        );
    }
}
```

**EventInterface handler** for this case an instance of _MessageCommand_

```php
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Context;

class Name extends MessageCommand
{
    protected string $name = 'name';

    public function execute(array $args = [])
    {
        $this->api()->replyTo(
            $this->ctx()->getChatId(),
            'Please give me your name:',
            $this->ctx()->getMessageId(),
        );

        // Register next conversation handler
        return nameConversation::new($this->ctx()->getChatId(), $this->ctx()->getUserId());
    }
}
```

Register the conversation

```php
$bot->onEvent(Name::get());
```

> For more details see [examples](examples/) folder

### Logging

#### Basic usage

```php
use Mateodioev\TgHandler\Log\{Logger, TerminalStream};

$log = new Logger(new TerminalStream); // Print logs in terminal
$bot->setLogger($log); // Save logger
```

#### Collections of streams

```php
use Mateodioev\TgHandler\Log\{BulkStream, Logger, TerminalStream, PhpNativeStream};

$bulkStream = new BulkStream(
    new TerminalStream(), // Print logs in terminal
    (new PhpNativeStream)->activate(__DIR__) // save logs in .log file and catch php warnings
);

$bot->setLogger(new Logger($bulkStream));
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
    
    public function execute(array $args = [])
    {
         $this->logger()->debug('Logging inside event');
         $this->logger()->info('User {name} use the command {commandName}', [
            'name'        => $this->ctx()->getUserName() ?? 'null',
            'commandName' => $this->name
         ]);
    }
}

// register the command
$bot->onEvent(MyCommand::get());
```

### Manager errors


You can register an exception handler the will be used if an exception occurs within the [Events::execute method](src/Events/EventInterface.php#87) method or in the event middlewares

```php

// Base exception
class UserException extends \Exception
{}

// Specific exception
final class UserNotRegistered extends UserException {}
final class UserBanned extends UserException {}

// This only manage UserBanned exception
$bot->setExceptionHandler(UserBanned::class, function (UserBanned $e, Bot $bot, Context $ctx) {
    $bot->getApi()->sendMessage($ctx->getChatId(), 'You are banned');
});

// This manage all UserException subclasses
$bot->setExceptionHandler(UserException::class, function (UserException $e, Bot $bot, Context $ctx) {
    $bot->getLogger()->warning('Occurs an user exception in chat id: ' . $ctx->getChatId());
});
```
