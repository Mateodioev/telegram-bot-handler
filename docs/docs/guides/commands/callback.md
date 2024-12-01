---
sidebar_position: 3
---

# Callback

When you send an [inline button](./../buttons#inline-button) with a callback_data the first string separated by space is treated as a command and can be assigned to a CallbackCommand.

```php
$this->api()->sendMessage('chat id', 'testing', [
    'reply_markup' => (string) ButtonFactory::inlineKeyboardMarkup()
        ->addCeil([
            'text' => 'User info',
            'callback_data' => 'user',
        ])
]);
```

In this example, when the user press the button "User info" the bot will try to find a CallbackCommand with the name `user` and execute it.

# Creating a CallbackCommand

You need to extend the `CallbackCommand` an set the `$name` property

```php
class UserInfo extends CallbackCommand
{
    protected string $name = 'user'; // same as the first string in the callback_data

    public function execute(array $args = [])
    {

    }
}
```

# Registering the command
Same as text command, are two ways to add the command

# onEvent
Default method

```php title="index.php"
$bot->onEvent(new UserInfo());
```

# registerCommand
Allows to use [GenericCommand](./generic.md)

```php title="index.php"
$bot->registerCommand(new UserInfo());
```
