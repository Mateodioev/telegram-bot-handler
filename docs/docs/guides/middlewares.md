---
sidebar_position: 4
---

# Middlewares

Middleware is executed before an event, and the result will send to the same event

# Registering a middleware
```php
use Mateodioev\TgHandler\Events\{EventType, abstractEvent};
class MyEvent extends abstractEvent
{
    public EventType $type = EventType::message;
    protected array $middlewares = [
        MyMiddlewareClass::class,
    ];

    public function execute(array $args = [])
    {
        // $args contain the result of all the middleware
    }
}
```

Also you can use the methods `addMiddleware`, `setMiddlewares`

```php
$event->addMiddleware(new MyMiddlewareClass()); // Add a single middleware
$event->setMiddlewares($myMiddlewareArray); // Register multiple middlewares
```

# Creating a new middleware
You need to extend the `Middleware` class


```php title="antispam.php"
use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Middleware\Middleware;

class AntiSpam extends Middleware
{
    public function __construct(
        private readonly int $maxMessages = 1,
        private readonly int $time = 60,
    ) {
    }

    public function __invoke(Context $ctx, Api $api, array $args = []): User
    {
        // $args contain the result of previously executed middlewares
        $user = Database::findUser($this->ctx()->getUserId());

        if ($user->throwAntispam($this->maxMessages, $this->time)) {
            // Stop the event execution
            throw new StopCommand('Please wait.');
        }

        return $user;
    }
}
```
> Note: The methods used here are fictitious

Add to the event

```php
class MyEvent extends abstractEvent
{
    protected array $middlewares = [
        AntiSpam::class,
    ];
}
```

# Acceding to the results of the middleware
`$args` contain the result of all the middlewares, if a middleware return nothing or null it wil not be registered.

Inside an event
```php title="start.php"
protected array $middlewares = [
    AntiSpam::class . ':4,60', // Max 4 messages every minute
];

public function execute(array $args = [])
{
    $user = $args[AntiSpam::class];
}
```

Inside another middleware
```php
public function __invoke(Context $ctx, Api $api, array $args = [])
{
    $args[AnotherMiddleware::class];
}
```

# Creating a middleware with custom values

If you middleware has a constructor an need some values you can use this syntax when registering the middleware on the event

```php
protected array $middlewares = [
    AntiSpam::class . ':4,60',
    // Add an initial colon ":"
    // Every parameter need to be separated by a coma ","
];
```