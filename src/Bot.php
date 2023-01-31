<?php

namespace Mateodioev\TgHandler;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\Bots\Telegram\Types\Update;
use Mateodioev\TgHandler\Commands\CommandInterface;
use Mateodioev\TgHandler\Log\{BotApiStream, Logger};
use Psr\Log\LoggerInterface;

class Bot
{
	protected Api $api;
    protected LoggerInterface $logger;

    /**
     * @var array<string|CommandInterface[]>
     */
	protected array $commands = [];

	public function __construct(string $token) {
		$this->api = new Api($token);
	}

	public function getApi(): Api
	{
		return $this->api;
	}

    public function setLogger(LoggerInterface $logger): Bot
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Set default logger class
     * @param string $chatId Chat to send logs
     */
    public function setDefaultLogger(string $chatId): Bot
    {
        $apiStream = new BotApiStream($this->getApi(), $chatId);
        return $this->setLogger(new Logger($apiStream));
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

		foreach ($ctxProperties as $type => $value) {
			if (!is_array($value))
				continue;
			
			$commands = $this->commands[$type] ?? [];
			foreach ($commands as $command) {
				try {
                    $command->setLogger($this->logger)
                        ->execute($this->api, $ctx);
				} catch (\Throwable $e) {
                    $this->logger->error('Fail to run command {name}, reason: {reason}', [
                        'name' => $command->getName(),
                        'reason' => $e->getMessage()
                    ]);
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
				$this->logger->warning('Fail to get updates: {reason}', ['reason' => $e->getMessage()]);
				continue;
			}

			foreach ($updates as $update) {
				$offset = $update->update_id() + 1;
				$this->run($update);
			}
		}
	}
}
