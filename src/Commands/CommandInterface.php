<?php

namespace Mateodioev\TgHandler\Commands;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Psr\Log\LoggerInterface;

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
    public function setDescription(string $description): void;

    public function setLogger(LoggerInterface $logger): static;
    public function getLogger(): LoggerInterface;

	/**
	 * Run command
	 */
	public function execute(Api $bot, Context $context);
}
