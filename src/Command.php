<?php

namespace Mateodioev\TgHandler;

interface Command
{
	/**
	 * Get commands name
	 */
	public function getCommands(): array;

	/**
	 * Get command prefix, can be: `!`, `/`, `.`, `,`, etc
	 */
	public function getPrefix(): array;

	/**
	 * Match a update if is a valid command
	 */
	public function match(string $update): bool;

	/**
	 * Get payload command, return `null` is empty
	 */
	public function getPayload(string $update): ?string;
}
