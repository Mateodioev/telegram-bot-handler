<?php

declare(strict_types=1);

use Mateodioev\Bots\Telegram\Config\ParseMode;
use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\FSM\{
    AbstractState,
    ConversationStateMachine,
    MessageFSMConversation,
    StateMachine,
    StateTransition
};

class FSMNameConversation extends MessageFSMConversation
{
    public const string NAME_TOKEN = '%d_fsmname';
    public const string AGE_TOKEN = '%d_fsmage';

    protected function createStateMachine(): StateMachine
    {
        $machine = new ConversationStateMachine(
            'name_conversation',
            $this->userId,
            $this->chatId
        );

        $nameState = new NameState('name', 'Ask for name');
        $ageState = new AgeState('age', 'Ask for age');
        $confirmState = new ConfirmState('confirm', 'Confirm details');
        $completeState = new CompleteState('complete', 'Conversation complete');

        $nameState->setConversation($this);
        $ageState->setConversation($this);
        $confirmState->setConversation($this);
        $completeState->setConversation($this)->setTerminal(true);

        $machine->addState($nameState);
        $machine->addState($ageState);
        $machine->addState($confirmState);
        $machine->addState($completeState);

        $machine->setInitialState('name');

        return $machine;
    }

    protected function onComplete(): void
    {
        $this->db()->delete(self::nameToken($this->userId));
        $this->db()->delete(self::ageToken($this->userId));
    }

    public static function nameToken(int $userId): string
    {
        return sprintf(self::NAME_TOKEN, $userId);
    }

    public static function ageToken(int $userId): string
    {
        return sprintf(self::AGE_TOKEN, $userId);
    }
}

class NameState extends AbstractState
{
    public function __construct(string $id, string $name)
    {
        parent::__construct($id, $name, 'Please enter your name', 'My name is {w:name}');
    }

    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $conversation->api()->sendMessage($ctx->getChatId(), 'What is your name?');
    }

    public function process(Context $ctx): StateTransition
    {
        $conversation = $this->getConversation();
        $name = $conversation->param('name');

        $conversation->db()->save(
            FSMNameConversation::nameToken($ctx->getUserId()),
            $name
        );

        return StateTransition::to('age');
    }
}

class AgeState extends AbstractState
{
    public function __construct(string $id, string $name)
    {
        parent::__construct($id, $name, 'Please enter your age', 'My age is {d:age}');
    }

    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $conversation->api()->sendMessage($ctx->getChatId(), 'What is your age?');
    }

    public function process(Context $ctx): StateTransition
    {
        $conversation = $this->getConversation();
        $age = $conversation->param('age');

        $conversation->db()->save(
            FSMNameConversation::ageToken($ctx->getUserId()),
            $age
        );

        return StateTransition::to('confirm');
    }
}

class ConfirmState extends AbstractState
{
    public function __construct(string $id, string $name)
    {
        parent::__construct($id, $name, 'Confirm your details', '{all:answer}');
    }

    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $name = $conversation->db()->get(FSMNameConversation::nameToken($ctx->getUserId()));
        $age = $conversation->db()->get(FSMNameConversation::ageToken($ctx->getUserId()));

        $conversation->api()->sendMessage(
            $ctx->getChatId(),
            "So {$name}, do you have {$age} years? (yes/no)"
        );
    }

    public function process(Context $ctx): StateTransition
    {
        $conversation = $this->getConversation();
        $answer = strtolower($conversation->param('answer', ''));

        $yesAnswers = ['yes', 'si', 'y', 'sí'];

        if (in_array($answer, $yesAnswers)) {
            return StateTransition::to('complete');
        }

        return StateTransition::to('age');
    }
}

class CompleteState extends AbstractState
{
    public function __construct(string $id, string $name)
    {
        parent::__construct($id, $name, 'Conversation completed', '');
    }

    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $name = $conversation->db()->get(FSMNameConversation::nameToken($ctx->getUserId()));
        $age = (int) $conversation->db()->get(FSMNameConversation::ageToken($ctx->getUserId()));

        $msg = 'Welcome ' . $ctx->getUser()->mention(customName: $name);
        if ($age < 18) {
            $msg .= ', you are still a minor';
        }

        $conversation->api()->sendMessage(
            $ctx->getChatId(),
            $msg,
            ['parse_mode' => ParseMode::HTML]
        );
    }

    public function process(Context $ctx): StateTransition
    {
        return StateTransition::to('complete');
    }
}
