<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

use function preg_match;

/**
 * Validate if the message text match with the pattern
 */
#[Attribute]
final class FilterMessageRegex implements Filter
{
    public function __construct(
        private readonly string $pattern
    ) {
    }

    public function apply(Context $ctx): bool
    {
        $text = $ctx->getMessageText() ?? '';

        return preg_match($this->pattern, $text) === 1;
    }
}
