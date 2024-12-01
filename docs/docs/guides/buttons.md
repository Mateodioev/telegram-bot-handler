---
sidebar_position: 6
---

# Buttons

Telegram support four types of buttons.
You can use the helper `ButtonFactory` to create buttons.

# Types

## Inline button

Button attached to the message sent

```php
use Mateodioev\Bots\Telegram\Buttons\ButtonFactory;
use Mateodioev\Bots\Telegram\Types\InlineKeyboardButton;

ButtonFactory::inlineKeyboardMarkup()
    ->addCeil([
        'text'          => 'Example button 1'
        'callback_data' => 'click some data'
    ])
    ->addLine()
    ->addCeil(
        InlineKeyboardButton::default()
            ->setText('Example button 1')
            ->setCallbackData('click some data')
    );
```
> This produce a button with 2 columns and one row.

The method `addCeil` add a button in the same row and `addLine` adds another column.

- `addCeil`: Supports an instance of `InlineKeyboardButton` or an array with the same properties
For the documentation of the parameters go to [telegram bot api](https://core.telegram.org/bots/api#inlinekeyboardmarkup)

## Reply keyboard

This button set a keyboard for the user with the values passed.

```php
use Mateodioev\Bots\Telegram\Buttons\ButtonFactory;
use Mateodioev\Bots\Telegram\Types\KeyboardButton;

ButtonFactory::replyKeyboardMarkup(
    isPersistent: false,
    resizeKeyboard: false,
    oneTimeKeyboard: false,
    inputFieldPlaceholder: null,
    selective: true,
)
    ->addCeil(['text' => 'Button 1'])
    ->addLine()
    ->addCeil(
        KeyboardButton::default()
            ->setText('Button 2')
    );
```
> This produce a button with 2 columns and one row.

The method `addCeil` add a button in the same row, and `addLine` adds another column.

- `addCeil`: Supports an instance of `KeyboardButton` or an array with the same properties.
For the documentation of the parameters go to [telegram bot api](https://core.telegram.org/bots/api#replykeyboardmarkup)

## Force reply

Telegram clients will display a reply interface to the user (act as if the user has selected the bot's message and tapped 'Reply').

```php
use Mateodioev\Bots\Telegram\Buttons\ButtonFactory;

ButtonFactory::forceReply();
```

## Keyboard remove

Telegram clients will remove the current custom keyboard and display the default letter-keyboard.

```php
use Mateodioev\Bots\Telegram\Buttons\ButtonFactory;

ButtonFactory::replyKeyboardRemove();
```

# Sending a button

Telegram methods like `sendMessage`, `editMessageText`, etc. supports buttons as the parameter `reply_markup`

```php
use Mateodioev\TgHandler\Events\{EventType, abstractEvent};

class MyEvent extends abstractEvent
{
    public EventType $type = EventType::message;

    public function execute(array $args = [])
    {
        $this->api()->sendMessage(
            chatID: $this->ctx()->getChatId(),
            text: 'Hello',
            params: [
                'reply_markup' => (string) ButtonFactory::inlineKeyboardMarkup()
                    ->addCeil([
                        'text'          => 'Example button 1'
                        'callback_data' => 'click some data'
                    ])
                    ->addLine()
                    ->addCeil(
                        InlineKeyboardButton::default()
                            ->setText('Example button 1')
                            ->setCallbackData('click some data')
                    );
            ]
        );
    }
}
```
> Example inside an event

When you convert a button to string, it will be convert to a json
