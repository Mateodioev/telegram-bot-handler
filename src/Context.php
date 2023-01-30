<?php

namespace Mateodioev\TgHandler;

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

	public static function fromUpdate(Update $update)
	{
		$up = json_encode($update->get());
		return new self(json_decode($up));
	}

    public function getMessageText(): string
    {
        return $this->message()->text() ?? '';
    }
}
