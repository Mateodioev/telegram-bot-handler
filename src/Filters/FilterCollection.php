<?php

namespace Mateodioev\TgHandler\Filters;

use Mateodioev\TgHandler\Context;

#[\Attribute]
final class FilterCollection implements Filter
{
	private array $filters = [];

	public function __construct(Filter ...$filters)
	{
		$this->filters = $filters;
	}

	public function apply(Context $ctx): bool
	{
		foreach ($this->filters as $filter) {
			if ($filter->apply($ctx) === false)
				return false;
		}

		return true;
	}
}
