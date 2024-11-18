---
sidebar_position: 2
---

# Text

Text commands only respond to text messages send by a users

# Basic usage

Start creating a class the extend the `MessageCommand` interface

```php
use Mateodioev\TgHandler\Commands\MessageCommand;

class Start extends MessageCommand {
    protected string $name = 'start';

    // Optional properties
    protected string $description = 'This is the start command';
    protected array $prefix = ['/', '!', '.'];
    protected array $alias = ['help'];

    public function execute(array $args = []) {
        // Here goes the logic of the command
    }
}
```

- `$prefix`: By the default the `MessageCommand` only works with the prefix `/`, you can set here an array with additional prefixes
- `$alias`: To call the command with other names 

Now this command only is executed when the user send a message like `/start` to the bot

## The `execute` method

This method contains all the logic of your command and only if execute when the given text match the `$name`

# Registering the command

There are two ways to register a command in the bot

## onEvent
This is the default way an not provide any type of advantage

```php title="index.php"
$bot->onEvent(new Start());
```

## registerCommand
This method allows you to use [GenericCommands](generic)

```php title="index.php"
$bot->registerCommand(new Start());
```

:::warning[Note]

All other commands will be registered with the add method.

```php title="index.php"
$bot->registerCommand(new Start())
    ->add(new Register())
    ->add(new User());
```

:::

# Methods

- `$this->api()`: Instance of [Telegram bot api](https://github.com/Mateodioev/telegram-bot-api/tree/v4)
- `$this->ctx()`: Object containing all the information of the [event](../events.md)
- `$this->ctx()->getPayload(): string`: The the text after the command, e.g: `/start hello my name is ...` -> `hello my name is ...`
