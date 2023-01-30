<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;

interface CommandInterface
{
	/**
	 * The name of the telegram command
	 */
	public function getName(): string;

	/**
	 * Command aliases
	 */
	public function getAliases(): array;

    /**
     * Get command description
     */
    public function getDescription(): string;

	/**
	 * Run command
	 */
	public function execute(Api $bot, Context $context);
}
