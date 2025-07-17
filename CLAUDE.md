# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

- **Run tests**: `composer test` or `./vendor/bin/phpunit --testdox tests/ --colors=always`
- **Code formatting**: `composer fix` or `./vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php -vv`
- **Install dependencies**: `composer install`

## Architecture Overview

This is a PHP library (`mateodioev/tg-handler`) for creating Telegram bots with a command-based architecture.

### Core Components

- **Bot class** (`src/Bot.php`): Main entry point that handles updates, manages events, and coordinates the entire bot lifecycle
- **Command System**: Commands extend abstract classes like `MessageCommand` or `CallbackCommand` and implement an `execute()` method
- **Event System**: Events implement `EventInterface` and are stored in `EventStorage` for processing
- **Middleware**: Middleware classes extend `Middleware` and can intercept command execution
- **Filters**: Attribute-based filters (e.g., `#[FilterPrivateChat]`) control when commands execute
- **Conversations**: Multi-step interactions using `MessageConversation` classes

### Key Directories

- `src/Commands/`: Command base classes and generics
- `src/Events/`: Event handling and types
- `src/Filters/`: Filter implementations for command validation
- `src/Middleware/`: Middleware system
- `src/Conversations/`: Multi-step conversation handling
- `src/Log/`: Logging streams and utilities
- `src/Db/`: Database abstraction layer
- `examples/`: Working examples of bot implementations

### Command Structure

Commands follow this pattern:
```php
class MyCommand extends MessageCommand
{
    protected string $name = 'commandname';
    protected array $prefix = ['/'];
    protected string $params = '{all:payload}?';
    
    public function execute(array $args = [])
    {
        // Command logic here
        $this->api()->sendMessage($this->ctx()->getChatId(), 'Response');
    }
}
```

### Bot Initialization

The bot is typically initialized with:
```php
$bot = new Bot($token, $logger);
$bot->onEvent(CommandClass::get());
$bot->byWebhook(); // or $bot->longPolling()
```

### Testing

- Uses PHPUnit for testing
- Test files are in `tests/` directory
- Tests follow the pattern `*Test.php`
- Run individual tests with: `./vendor/bin/phpunit tests/path/to/TestFile.php`

### Dependencies

- **Core**: `mateodioev/tgbot` for Telegram API, `psr/log` for logging
- **Dev**: `phpunit/phpunit` for testing, `friendsofphp/php-cs-fixer` for code formatting
- **Async**: Uses `amphp/*` packages for asynchronous operations