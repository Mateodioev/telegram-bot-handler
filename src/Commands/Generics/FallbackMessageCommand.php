<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Commands\Generics;

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Commands\MessageCommand;
use Mateodioev\TgHandler\Context;

/**
 * Command to execute when cant find a valid command
 * @internal
 */
final class FallbackMessageCommand implements FallbackCommand
{
    private array $commands = [];

    public function handle(Api $bot, Context $context): void
    {
        $prefix = $context->getMessageText()[0];
        $cmds   = 'Hi ' . $context->getUser()->mention() . ", this is a the list of commands available:\n\n";

        foreach ($this->commands() as $name => $cmd) {
            $cmds .= '> ' . $prefix . $name;

            // Add params if is not empty
            if (($params = $cmd->params()) !== MessageCommand::DEFAULT_PARAMS) {
                $cmds .= ' <code>' . $params . '</code>';
            }

            // Only add description if is not empty
            if (empty($description = $cmd->description()) === false) {
                $cmds .= ' - <i>' . $description . '</i>';
            }
            // Command aliases
            if (($aliases = $cmd->getAliases()) !== []) {
                $cmds .= "\n  <b>Aliases:</b> " . join(', ', $aliases);
            }
            $cmds .= "\n";
        }

        $bot->replyToMessage($context->message(), $cmds);
    }

    /**
     * @return MessageCommand[]
     */
    public function commands(): array
    {
        return $this->commands;
    }

    /**
     * @param MessageCommand[] $command
     */
    public function setCommands(array $commands): static
    {
        $this->commands = $commands;
        return $this;
    }
}
