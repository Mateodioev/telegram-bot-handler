<?php

namespace Mateodioev\TgHandler\Filters;

use Mateodioev\TgHandler\Context;

/**
 * This filter validate the user id of the message
 */
#[\Attribute]
final class FilterFromUserId implements Filter
{
	public function __construct(
		private int $userId
	) {
	}

	public function apply(Context $ctx): bool
	{
		return $this->userId === $ctx->getUserId();
	}
}
