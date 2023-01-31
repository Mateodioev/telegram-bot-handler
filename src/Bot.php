<?php

namespace Mateodioev\TgHandler;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Interfaces\TypesInterface;
use Mateodioev\Bots\Telegram\Types\Update;
use Mateodioev\TgHandler\Commands\CommandInterface;
use stdClass;

class Bot
{
	protected Api $api;

	protected array $commands = [];

	public function __construct(string $token) {
		$this->api = new Api($token);
	}

	public function getApi(): Api
	{
		return $this->api;
	}

	public function on(string $type, CommandInterface $command): Bot
    {
		$this->commands[$type][] = $command;
        return $this;
	}

	public function run(Update $update): void
    {
		$ctx = Context::fromUpdate($update);
		// Get context properties as array
		$ctxProperties = $ctx->get();
		echo 'Executing update_id: '.$update->updateId().PHP_EOL;

		foreach ($ctxProperties as $type => $value) {
			if (!is_array($value))
				continue;
			
			$commands = $this->commands[$type] ?? [];
			foreach ($commands as $command) {
				echo 'Executing command: '.$command->getName().PHP_EOL;
				// Run command

				try {
					$command->execute($this->api, $ctx);
				} catch (\Throwable $e) {
					echo $e->getMessage().PHP_EOL;
				}
			}
		}
	}

	public function byWebhook(): void
    {
		$update = json_decode(
			file_get_contents('php://input')
		);
		$update = new Update($update);

		$this->run($update);
	}

	public function longPolling(int $timeout): never
	{
		$offset = 0;

		while (true) {
			// Get updates only for registered commands
			$allowedUpdates = \array_keys($this->commands);

			try {
				$updates = $this->api->getUpdates($offset, 100, $timeout, $allowedUpdates);
			} catch (\Throwable $e) {
				echo $e->getMessage().PHP_EOL;
				continue;
			}

			foreach ($updates as $update) {
				$offset = $update->update_id() + 1;
				$this->run($update);
			}
		}
	}
}
