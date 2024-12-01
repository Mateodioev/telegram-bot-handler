---
sidebar_position: 5
---

# Filters

Filter are similar to [middlewares](./middlewares) but with the difference that only has access to the context an no the api.
It is intended to perform lighter validations, such as verifying the type of chat, verifying if a message contains multimedia, etc.

# Adding a filter to an event
Filters are treated like [Attributes](https://www.php.net/manual/en/language.attributes.overview.php).

```php title="start.php"
use Mateodioev\TgHandler\Filters\FilterPrivateChat;

#[FilterPrivateChat]
class Start extends MessageCommand {
    ///
}
```

# Pre defined filters

Import all the filters:
```php
use Mateodioev\TgHandler\Filters\{
    FilterChatType,
    FilterFromUserId,
    FilterIgnore,
    FilterMessageChat,
    FilterMessageMedia,
    MediaType,
    FilterMessageMediaSticker,
    FilterPrivateChat,
    FilterNot,
    FilterOr,
    FilterXor,
    FilterCollection
};
```

- `FilterChatType`: Validate that the update is from a specific [chat type](https://core.telegram.org/bots/api#chat)
  ```php
  #[FilterChatType(chatType: 'private')]
  ```
- `FilterFromUserId`: Validate that the update is coming from a specific user
  ```php
  #[FilterFromUserId(userId: 'MY PERSONAL ID')]
  ```
- `FilterIgnore`: Always return false
- `FilterMessageChat`: Validate that the update is coming from a specific chat
  ```php
  #[FilterMessageChat(chatId: 'MY CHAT ID')]
  ```
- `FilterMessageMedia`: Validate that the event is a message and validate the it has the specified media type.
  ```php
  #[FilterMessageMedia(mediaType: MediaType::video)]
  ```
- `FilterMessageMediaSticker`: Validate that the event contain a sticker.
  ```php
  #[FilterMessageMediaSticker]
  ```
- `FilterPrivateChat`: Validate that the update is coming from a private chat (e.g: User talks to the bot)
  #[FilterPrivateChat]
- `FilterNot`: Negate the filter
  ```php
  // The event works in any type of chat, except private chats
  #[FilterNot(new FilterPrivateChat)]
  ```
- `FilterOr`: Return `true` is either $a or $b is `true`
  ```php
  // Works with audio or voice
  #[FilterOr(a: new FilterMessageMedia(MediaType::audio), b: FilterMessageMedia(MediaType::voice))]
  ```
- `FilterXor`: Return `true` if either $a or $b is `true, but not both
  ```php
  // Works with document or photo, but not both
  #[FilterXor(a: new FilterMessageMedia(MediaType::document), b: FilterMessageMedia(MediaType::photo))]
  ```
- `FilterCollection`: Groups multiple filters

# Using multiple filters

You can use `FilterCollection` or you just use the [Attributes syntax](https://www.php.net/manual/en/language.attributes.overview.php)

```php
use Mateodioev\TgHandler\Events\Types\MessageEvent;

#[FilterPrivateChat]
#[FilterMessageRegex('/.*(filters).*/i')]
class MyEvent extends MessageEvent {
    //
}
```
> Now this only works with private chats **and** texts containing the work `filters`

# On invalid filters

If you want to make some action when any of the filters can be validate you can implement the method `onInvalidFilters` in your [command](./commands/intro)

> Note: I'm working on move this to the event interface

```php
use Mateodioev\TgHandler\Commands\MessageCommand;

#[FilterPrivateChat]
class MyEvent extends MessageCommand {
    public function execute(array $args = [])
    {
        // Your logic here
    }

    public function onInvalidFilters(): ?bool
    {
        $id = $this->ctx()->getUserId();

        // fake method to check if this user can use private commands
        if (isAllowed($id)) {
            return true;
        } else {
            $this->api()->sendMessage($this->ctx()->getChatId(), 'Only execute this command in a private chat');
            return false;
        }
    }
}
```

## Understanding the return value

- `true`: Continue with the `execute` method
- `false`: Terminate the execution of the event
- `null`: Like false, but the command is treated as not execute
