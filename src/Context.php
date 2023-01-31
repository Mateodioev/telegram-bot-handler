<?php

namespace Mateodioev\TgHandler;

use Mateodioev\Bots\Telegram\Types\Document;
use Mateodioev\Bots\Telegram\Types\Update;
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

	public function getUserId(): ?int
	{
		return $this->message()->from()->id()
			?? $this->callbackQuery()->from()->id()
			?? $this->inlineQuery()->from()->id();
	}

	public function getUserName(): ?string
	{
		return $this->message()->from()->username()
			?? $this->callbackQuery()->from()->username()
			?? $this->inlineQuery()->from()->username();
	}

	public function getChatId(): ?int
	{
		return $this->message()->chat()->id()
			?? $this->callbackQuery()->message()->chat()->id();
	}

	public function getMessageId(): ?int
	{
		return $this->message()->messageId()
			?? $this->callbackQuery()->message()->messageId();
	}

    public function getMessageText(): ?string
    {
        return $this->message()->text()
			?? $this->callbackQuery()->data()
			?? $this->inlineQuery()->query();
    }

	public function getChatType(): ?string
	{
		return $this->message()->chat()->type()
			?? $this->callbackQuery()->message()->chat()->type();
	}

	public function getFullName(): string
	{
		return trim($this->getFirsName() . ' ' . $this->getLastName());
	}

	public function getFirsName(): ?string
	{
		return $this->message()->from()->firstName()
			?? $this->callbackQuery()->from()->firstName()
			?? $this->message()->replyToMessage()->from()->firstName()
			?? $this->inlineQuery()->from()->firstName();
	}

	public function getLastName(): ?string
	{
		return $this->message()->from()->lastName()
			?? $this->callbackQuery()->from()->lastName()
			?? $this->message()->replyToMessage()->from()->lastName()
			?? $this->inlineQuery()->from()->lastName();
	}

	public function getDocument(): ?Document
	{
		return $this->message()->document()
			?? $this->message()->replyToMessage()->document();
	}
}
