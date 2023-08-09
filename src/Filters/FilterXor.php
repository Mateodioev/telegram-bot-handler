<?php

namespace Mateodioev\TgHandler\Filters;

use Attribute;
use Mateodioev\TgHandler\Context;

/**
 * Return true is `$a` or `$b` return true after call method {@see Filter::apply}, but if both return true, `apply` return false
 */
#[Attribute]
final class FilterXor implements Filter
{
	function __construct(
		private readonly Filter $a,
		private readonly Filter $b
	) {
	}

	public function apply(Context $ctx): bool
	{
		return $this->a->apply($ctx) XOR $this->b->apply($ctx);
	}
}
