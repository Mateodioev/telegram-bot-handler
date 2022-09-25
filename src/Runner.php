<?php

namespace Mateodioev\TgHandler;

use Mateodioev\Bots\Telegram\Methods;
use Mateodioev\PhpEasyCli\App as CliApp;
use Mateodioev\PhpEasyCli\Color;
use Mateodioev\Utils\Exceptions\RequestException;
use stdClass;
use Throwable;

class Runner
{
    private array $getUpdate = [
        'offset' => 0
    ];
    private mixed $afterMiddleware = null;
    private CliApp $cli;

    public Methods $bot;
    public Commands $commands;
    public bool $log = false;
    public bool $printLogs = false;
    public array $logs = [];

    public function __construct(Commands $cmd)
    {
        $this->commands = $cmd;
        $this->cli = new CliApp();
    }

    public function setBot(Methods $bot): Runner
    {
        $this->bot = $bot;
        return $this;
    }

    public function setCliApp(CliApp $app): Runner
    {
      $this->cli = $app;
      return $this;
    }

    /**
     * @param mixed|null $middleware Function to execute after all commands are called
     * @return Runner
     */
    public function setAfterMiddleware(mixed $middleware = null): Runner
    {
        $this->afterMiddleware = $middleware;
        return $this;
    }

    /**
     * Get json payload
     * @return stdClass
     */
    private function getBodyRaw(): stdClass
    {
        try {
            return json_decode(file_get_contents('php://input'));
        } catch (Throwable) {
            // Empty update
            return new stdClass();
        }
    }

    public function activateLog(bool $print = false): Runner
    {
        if (php_sapi_name() != 'cli') {
            return $this;
        }

        $this->log = true;
        $this->printLogs = $print;
        return $this;
    }

    public function disableLog(bool $print = false): Runner
    {
        $this->log = false;
        $this->printLogs = $print;
        return $this;
    }

    protected function log(string $message, string $type = 'INFO'): Runner
    {
        $type = strtoupper($type);

        $colorCodes = [
            'FATAL' => [9, 11],
            'ERROR' => [52, 145],
            'WARN'  => [239, 231],
            'INFO'  => [51, 4]
        ];
        if ($this->log) {
            $color = $colorCodes[$type] ?? [];

            $this->logs[$type][] = '[' . date('c') . '] ' . $message . PHP_EOL;
            $this->cli->getPrinter()
                ->out(Color::Bg($color[0], Color::Fg($color[1], '['.$type.']')).' ' . Color::Fg($color[1], $message))
                ->newLine();
        }
        return $this;
    }

    public function clearLogs(): Runner
    {
        $this->logs = [];
        return $this;
    }

    /**
     * Run the bot by webhook
     */
    public function runWebhook()
    {
        $up = $this->getBodyRaw();
        $this->commands->setUpdate($up);
        return $this->realRun($this->afterMiddleware);
    }

    public function longPolling(int $timeout = 10)
    {
        $this->getUpdate['timeout'] = $timeout;

        try {
            $updates = $this->bot->getUpdates($this->getUpdate);
            $this->log('Calling updates');
        } catch (RequestException $e) {
            $this->log($e->getMessage(), 'ERROR');
            usleep($timeout);
            $this->longPolling($timeout);
        }

        // Float
        if (!$updates->ok && $updates->error_code == 429) {
            $this->log($updates->description, 'FATAL');
            sleep($updates->parameters->retry_after);
        // Fail to get updates
        } elseif (!$updates->ok) {
            $this->log('Error: ' . $updates->description, 'FATAL');
            return 0;
        } else {
            foreach ($updates->result as $up) {
                $this->getUpdate['offset'] = $up->update_id + 1;
                $this->log('Poll id: ' . $up->update_id);
                $this->commands->setUpdate($up);
                $this->log('Function result: ' . $this->realRun());
            }
        }
        $this->longPolling($timeout);
    }

    /**
     * Secure run bot
     * @return mixed
     */
    protected function realRun(): mixed
    {
        try {
            $this->commands->Run($this->afterMiddleware);
            return $this->commands->getFnResult();
        } catch (Throwable $th) {
            $this->log($th->getMessage(), 'ERROR');
            return null;
        }
    }
}
