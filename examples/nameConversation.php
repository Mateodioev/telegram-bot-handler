<?php

use Mateodioev\Bots\Telegram\Api;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\MessageConversation;

class nameConversation extends MessageConversation
{
    const NAME_TOKEN = '%d_nameconversation';
    protected string $format = 'My name is {w:name}';
    public function execute(Api $bot, Context $context, array $args = [])
    {
        $name = $this->param('name');
        $this->db()->save(self::nameToken($context->getUserId()), $name);

        $bot->sendChatAction($context->getChatId(), 'typing');

        $bot->replyTo($context->getChatId(), 'Nice to meet you ' . $name, $context->getMessageId());
        $bot->sendMessage($context->getChatId(), 'What is your age? ');

        return ageConversation::fromContext($context);
        // return ageConversation::new($context->getChatId(), $context->getUserId());
    }

    /**
     * Get db key to save the name
     */
    public static function nameToken(int $userId): string
    {
        return \sprintf(self::NAME_TOKEN, $userId);
    }
}

class ageConversation extends MessageConversation
{
    const NAME_TOKEN = '%d_ageconversation';
    protected string $format = 'My age is {d:age}';

    public function execute(Api $bot, Context $context, array $args = [])
    {
        $bot->sendChatAction($context->getChatId(), 'typing');

        $age = $this->param('age');
        $name = $this->db()->get(nameConversation::nameToken($context->getUserId()));

        $bot->sendChatAction($context->getChatId(), 'typing');
        $bot->replyTo(
            $context->getChatId(),
            'So ' . $name . ', do you have ' . $age . ' years?',
            $context->getMessageId()
        );

        $this->db()->save(self::ageToken($context->getUserId()), $age);
        return confirmConversation::fromContext($context);
        // return confirmConversation::new($context->getChatId(), $context->getUserId());
    }

    public static function ageToken(int $userId)
    {
        return \sprintf(self::NAME_TOKEN, $userId);
    }
}

class confirmConversation extends MessageConversation
{
    public function execute(Api $bot, Context $context, array $args = [])
    {
        $userId = $context->getUserId();
        $bot->sendChatAction($context->getChatId(), 'typing');

        $age = (int) $this->db()->get(ageConversation::ageToken($userId));
        $name = $this->db()->get(nameConversation::nameToken($userId));

        $yes = ['yes', 'si', 'y'];

        if (\in_array(\strtolower($context->getMessageText()), $yes) === false) {
            // ask your age again
            $bot->sendMessage($context->getChatId(), 'What is your age? ');
            return ageConversation::fromContext($context);
            // return ageConversation::new($context->getChatId(), $context->getUserId());
        }

        $msg = 'Welcome ' . $name;
        if ($age < 18)
            $msg .= ', you are still a minor';

        $bot->sendMessage($context->getChatId(), $msg);
        $this->deleteDb($userId); // Clear this conversation data
    }

    private function deleteDb(int $userID): void
    {
        $this->db()->delete(nameConversation::nameToken($userID));
        $this->db()->delete(ageConversation::ageToken($userID));
    }
}
