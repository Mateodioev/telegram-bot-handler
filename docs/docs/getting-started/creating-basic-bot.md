---
sidebar_position: 3
---

# Creating a basic bot

Create a `index.php` or the name of your choice.

```php title="index.php"
<?php declare(strict_types=1)

use Mateodioev\TgHandler\Bot;

require __DIR__ . '/vendor/autoload.php';

$bot = new Bot('YOUR BOT TOKEN', $logger);
```

Where logger is a instance of [PSR logger](https://www.php-fig.org/psr/psr-3/), example:

```php
use Mateodioev\TgHandler\Log\{Logger, TerminalStream};
$logger = new Logger(new TerminalStream());
```
> You can see more about loggers in the [logger page](../guides/logger.md).

## Adding a basic command

To create a [command](../guides/commands/intro)([event](../guides/events)) to heard to all the `/start` text create the following file

```php title="start.php"
use Mateodioev\TgHandler\Commands\MessageCommand;

class Start extends MessageCommand {
  protected string $name = 'start';
  protected string $description = 'Start the bot';
    
  public function execute(array $args = []){
    $this->api()->sendMessage($this->ctx()->getChatId(), 'Hello!!!');
  }
}
```

:::tip[Note]

To include this command in your `index.php` you can use composer autoloader or just include it using `require` or `include` php functions.

:::

Now add it to your bot

```php title="index.php"
$bot->onEvent(new Start());
```

# Running the bot

If everything is ok, you can run the bot with the following method:
```php title="index.php"
$bot->longPolling(timeout: 60);
```

Also open a terminal an type

```bash
php index.php
```

> See more ways to run your bot in [Running methods](../guides/run-methods)