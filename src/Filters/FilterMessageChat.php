<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * This filter validate the chat id of the message
 */
#[Attribute]
final class FilterMessageChat implements Filter
{
	public function __construct(
		private readonly int|string $chatId
	) {
	}

	public function apply(Context $ctx): bool
	{
		$fromChat = $ctx->getChatId();

		return $this->chatId === $fromChat;
	}
}
