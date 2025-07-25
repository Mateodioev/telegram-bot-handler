<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * Validate if the message is from the user id specified
 */
#[Attribute]
final readonly class FilterFromUserId implements Filter
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
