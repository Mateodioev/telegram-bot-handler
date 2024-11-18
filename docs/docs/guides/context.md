---
sidebar_position: 2
---

# Context

This is a special object that contain all information of the update.
You can access the context inside the [execute method](./events#creating-a-new-event).

The context contain information about the `EvenType`, user, chat, message, etc.

# Common methods

```php
public function execute(array $args = [])
{
    $this->ctx()->eventType(); // The type of this event
    $this->ctx()->getUser();
    $this->ctx()->getChatId();
    $this->ctx()->getMessageId();
    $this->ctx()->getMessageText();
    $this->ctx()->getChatType();
    $this->ctx()->getPayload();
}
```

Also you can access al the method of the [Update](https://github.com/Mateodioev/telegram-bot-api/blob/v4/src/Types/Update.php) object
