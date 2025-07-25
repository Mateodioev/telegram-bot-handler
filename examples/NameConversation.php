<?php

/** @noinspection PhpInconsistentReturnPointsInspection */

declare(strict_types=1);

use Mateodioev\Bots\Telegram\Config\ParseMode;
use Mateodioev\TgHandler\Conversations\MessageConversation;

class NameConversation extends MessageConversation
{
    public const string NAME_TOKEN = '%d_nameconversation';
    protected string $format = 'My name is {w:name}';
    public function execute(array $args = [])
    {
        $name = $this->param('name');
        $this->db()->save(self::nameToken($this->ctx()->getUserId()), $name);

        $this->api()->sendChatAction($this->ctx()->getChatId(), 'typing');

        $this->api()->replyTo($this->ctx()->getChatId(), 'Nice to meet you ' . $name, $this->ctx()->getMessageId());
        $this->api()->sendMessage($this->ctx()->getChatId(), 'What is your age? ');

        return AgeConversation::fromContext($this->ctx());
    }

    /**
     * Get db key to save the name
     */
    public static function nameToken(int $userId): string
    {
        return sprintf(self::NAME_TOKEN, $userId);
    }
}

class AgeConversation extends MessageConversation
{
    public const string NAME_TOKEN = '%d_ageconversation';
    protected string $format = 'My age is {d:age}';

    public function execute(array $args = [])
    {
        $this->api()->sendChatAction($this->ctx()->getChatId(), 'typing');

        $age = $this->param('age');
        $name = $this->db()->get(NameConversation::nameToken($this->ctx()->getUserId()));

        $this->api()->sendChatAction($this->ctx()->getChatId(), 'typing');
        $this->api()->replyTo(
            $this->ctx()->getChatId(),
            'So ' . $name . ', do you have ' . $age . ' years?',
            $this->ctx()->getMessageId()
        );

        $this->db()->save(self::ageToken($this->ctx()->getUserId()), $age);
        return ConfirmConversation::fromContext($this->ctx());
    }

    public static function ageToken(int $userId): string
    {
        return sprintf(self::NAME_TOKEN, $userId);
    }
}

class ConfirmConversation extends MessageConversation
{
    public function execute(array $args = [])
    {
        $userId = $this->ctx()->getUserId();
        $this->api()->sendChatAction($this->ctx()->getChatId(), 'typing');

        $age = (int) $this->db()->get(AgeConversation::ageToken($userId));
        $name = $this->db()->get(NameConversation::nameToken($userId));

        $yes = ['yes', 'si', 'y'];

        if (in_array(strtolower($this->ctx()->getMessageText()), $yes) === false) {
            // ask your age again
            $this->api()->sendMessage($this->ctx()->getChatId(), 'What is your age? ');
            return AgeConversation::fromContext($this->ctx());
        }

        $msg = 'Welcome ' . $this->ctx()->getUser()->mention(customName: $name);
        if ($age < 18) {
            $msg .= ', you are still a minor';
        }

        $this->api()->sendMessage($this->ctx()->getChatId(), $msg, ['parse_mode' => ParseMode::HTML]);
        $this->deleteDb($userId); // Clear this conversation data
    }

    private function deleteDb(int $userID): void
    {
        $this->db()->delete(NameConversation::nameToken($userID));
        $this->db()->delete(AgeConversation::ageToken($userID));
    }
}
