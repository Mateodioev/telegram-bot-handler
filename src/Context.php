<?php

namespace Mateodioev\TgHandler;

use Mateodioev\Bots\Telegram\Types\{
	Document,
	Update,
	User
};

use stdClass;

/**
 * Telegram context
 */
class Context extends Update
{
    public function __construct(?stdClass $up)
    {
        parent::__construct($up);
    }

	public static function fromUpdate(Update $update): Context
    {
		$up = json_encode($update->get());
		return new self(json_decode($up));
	}

	public function getUser(): ?User
	{
		return $this?->message()?->from()
			?? $this?->callbackQuery()?->from()
			?? $this?->inlineQuery()?->from()
            ?? null;
	}

	public function getUserId(): ?int
	{
		return $this->getUser()?->id();
	}

	public function getUserName(): ?string
	{
		return $this->getUser()?->username();
	}

	public function getChatId(): ?int
	{
		return $this?->message()?->chat()?->id()
			?? $this?->callbackQuery()?->message()?->chat()?->id()
            ?? null;
	}

	public function getMessageId(): ?int
	{
		return $this?->message()?->messageId()
			?? $this?->callbackQuery()?->message()?->messageId()
            ?? null;
	}

    public function getMessageText(): ?string
    {
        return $this?->message()?->text()
			?? $this?->callbackQuery()?->data()
			?? $this?->inlineQuery()?->query()
            ?? null;
    }

	public function getPayload(): string
    {
        $text = $this->getMessageText() ?? '';
        $command = \explode(' ', $text)[0] ?? '';
        return \substr($text, \strlen($command) + 1);
    }

	public function getChatType(): ?string
	{
		return $this?->message()?->chat()?->type()
			?? $this?->callbackQuery()?->message()?->chat()?->type()
            ?? null;
	}

	public function getFullName(): string
	{
		return trim($this->getFirsName() . ' ' . $this->getLastName());
	}

	public function getFirsName(): ?string
	{
		return $this?->message()?->from()?->firstName()
			?? $this?->callbackQuery()?->from()?->firstName()
			?? $this?->message()?->replyToMessage()?->from()?->firstName()
			?? $this?->inlineQuery()?->from()?->firstName()
            ?? null;
	}

	public function getLastName(): ?string
	{
		return $this?->message()?->from()?->lastName()
			?? $this?->callbackQuery()?->from()?->lastName()
			?? $this?->message()?->replyToMessage()?->from()?->lastName()
			?? $this?->inlineQuery()?->from()?->lastName()
            ?? null;
	}

	public function getDocument(): ?Document
	{
		return $this?->message()?->document()
			?? $this?->message()?->replyToMessage()?->document()
            ?? null;
	}
}
