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

$bot->on('message', YourCommandInstance);

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

$bot->on('message', MyCommand::get());
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