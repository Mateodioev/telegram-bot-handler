---
sidebar_position: 1
---

# Events

When telegram send any type of [update](https://core.telegram.org/bots/api#update), this library converts it to an instance of the [Update](https://github.com/Mateodioev/telegram-bot-api/blob/v4/src/Types/Update.php) object, an to listen to it you need to create a event of the update type

## Event types

All the current types of supported events is defined in the enum [`Mateodioev\TgHandler\Events\EventType`](https://github.com/Mateodioev/telegram-bot-handler/blob/v5/src/Events/EventType.php):
- `EventType::message` Message sent by a user
- `EventType::callback_query` User press a inline button
- `EventType::edited_message`
- `EventType::channel_post`
- `EventType::edited_channel_post`
- `EventType::inline_query`
- `EventType::chosen_inline_result`
- `EventType::shipping_query`
- `EventType::poll`
- `EventType::poll_answer`
- `EventType::my_chat_member`
- `EventType::chat_join_request`
- `EventType::none` Just for ignore an event
- `EventType::all` Special type to listen any type of update

> To see the description of every type go to [telegram documentation](https://core.telegram.org/bots/api#update)

# Creating a new event

```php
use Mateodioev\TgHandler\Events\{EventType, abstractEvent};
// Event to hear any text message sent by a user, channel, etc
class MyEvent extends abstractEvent
{
    public EventType $type = EventType::message;

    public function execute(array $args = [])
    {
        // Event logic here
        $this->api()->sendMessage(
            chatID: 'YOUR TARGET ID',
            text: 'Hello',
        );
    }
}
```

In this event we set the property `$type` to a specific event type. To listen more events just create another event and add it to the bot.

> To access to the Update information see [Context](./context) object
# All event

This is a special event that serves to listen to any type of event.

```php
use Mateodioev\TgHandler\Events\Types\AllEvent;
class GlobalListener extends AllEvent
{
    public function execute(array $args = [])
    {
        $type = $this->ctx()->eventType()->prettyName();
        $raw = json_encode($this->ctx()->getReduced(), JSON_PRETTY_PRINT) . PHP_EOL;
        echo $raw;
    }
}
```

## Adding the event to the bot

```php
$bot->onEvent(new MyEventName());
```

# Stopping the execution of the event

Throw the special `StopCommand` exception

```php
use Mateodioev\TgHandler\Commands\StopCommand;

public function execute(array $args = [])
{
    throw new StopCommand();
}
```

:::tip[Note]

If you set a message, the bot will try to send the same message to the user
```php
throw new StopCommand('You can\'t use this command');
```
> Use html format.
:::