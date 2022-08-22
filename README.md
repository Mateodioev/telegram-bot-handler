# Telegram bot handler

## Installation

```bash
composer require mateodioev/tg-handler
```

## Usage

See [examples](/examples/) folder for more information.

```php
use Mateodioev\TgHandler\Commands;
$cmd = new Commands($namespace, $commandsPrefix);
```

### Register commands

Text commands
```php
$cmd->CmdMessage($cmd, $callable, $functionParams);
```

Callback data
```php
$cmd->CmdCallback($cmd, $callable, $functionParams);
```

Inline querys
```php
$cmd->CmdInline($cmd, $callable, $functionParams);
```

### Middlewares

Middlewares are executed before the command is executed.

_Types:_ message, photo, video, audio, voice, documment, sticker, venue, location, inline, callback, new_chat_member, left_chat_member, new_chat_title, new_chat_photo, group_chat_created, supergroup_chat_created, migrate_to_chat_id, migrate_from_chat_id, edited, game, channel, edited_channel
```php
$cmd->on($type, $callable, $functionParams);
```

### Run commands

Run all comands registered and execute after middleware.

```php
// Optional param
$cmd->run($afterMidleware);
```