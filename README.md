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