<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Log;

use App\Models\Tools\ArrayWrapper;
use Mateodioev\Bots\Telegram\Api;
use SimpleLogger\streams\LogResult;

use function str_replace;

/**
 * Push messages to telegram channel/chat
 */
class BotApiStream implements Stream
{
    use BitwiseFlag;

    public function __construct(
        protected Api $api,
        protected string $chatId
    ) {
        $this->setLevel(Logger::CRITICAL | Logger::ERROR | Logger::EMERGENCY);
    }

    public function setLevel(int $level, bool $add = true): static
    {
        $this->setFlag($level, $add);
        return $this;
    }

    public function push(LogResult $message, ?string $level = null): void
    {
        if ($this->isFlagSet(Logger::levelToInt($message->level ?? '')) === false) {
            return;
        }

        $level = \strtoupper($message->level);
        $strMessage = $this->replaceIllegalCharacters($message->message);
        $messages = self::chunkString($strMessage, 1000);

        array_walk($messages, function (string &$message) use ($level) {
            $message = "<b>{$level}</b>\n<pre>{$message}</pre>";
            $this->api->sendMessage($this->chatId, $message, ['parse_mode' => 'html']);
        });
    }

    protected function replaceIllegalCharacters(string $message): string
    {
        return str_replace(['<', '>'], ['&lt;', '&gt;'], $message);
    }

    /**
     * @return string[]
     */
    private static function chunkString(string $str, int $maxLength = 1024): array
    {
        $result = [];
        $textLength = strlen($str);
        $position = 0;

        while ($position < $textLength) {
            // Si la posición + longitud supera el texto, tomar lo que quede
            if ($position + $maxLength >= $textLength) {
                $result[] = substr($str, $position);
                break;
            }

            // Buscar un punto de ruptura adecuado
            $puntoCorte = $position + $maxLength;

            // Verificar si estamos en medio de una palabra
            if (!in_array($str[$puntoCorte], [' ', ',', '.', ';', ':', "\n", "\r", "\t"])) {
                // Retroceder hasta encontrar un delimitador
                $haystack = substr($str, $position, $maxLength);
                $ultimoDelimitador = max(
                    strrpos($haystack, ' '),
                    strrpos($haystack, ','),
                    strrpos($haystack, '.'),
                    strrpos($haystack, ';'),
                    strrpos($haystack, ':'),
                    strrpos($haystack, "\n"),
                    strrpos($haystack, "\r"),
                    strrpos($haystack, "\t")
                );

                if ($ultimoDelimitador !== false) {
                    $puntoCorte = $position + $ultimoDelimitador + 1;
                }
            }

            $result[] = substr($str, $position, $puntoCorte - $position);
            $position = $puntoCorte;
        }

        return $result;
    }
}
