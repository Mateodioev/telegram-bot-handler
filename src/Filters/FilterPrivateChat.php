<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * This filter validates that the chat is private
 */
#[Attribute]
final class FilterPrivateChat implements Filter
{
	public function apply(Context $ctx): bool
	{
		return $ctx->getChatType() === 'private';
	}
}
