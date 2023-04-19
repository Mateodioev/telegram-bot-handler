<?php

namespace Mateodioev\TgHandler\Log;

/**
 * @see https://www.php.net/manual/en/language.operators.bitwise.php#108679
 */
trait BitwiseFlag
{
  	protected int $flags = 0;

	protected function isFlagSet($flag): bool {
		return (($this->flags & $flag) == $flag);
	}

	protected function setFlag(int $flag, bool $value) {
		if ($value) {
			$this->flags |= $flag;
		} else {
			$this->flags &= ~$flag;
		}
	}
}
