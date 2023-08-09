<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;
use function preg_match;

/**
 * This filter validate the regex in the messages
 */
#[Attribute]
final class FilterMessageRegex implements Filter
{
	public function __construct(
		private readonly string $regex
	) {
	}

	public function apply(Context $ctx): bool
	{
		$text = $ctx->getMessageText() ?? '';

		return preg_match($this->regex, $text) === 1;
	}
}
