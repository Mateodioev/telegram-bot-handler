<?php

declare(strict_types=1);

use Mateodioev\TgHandler\Context;
use Mateodioev\TgHandler\Conversations\FSM\{
    AbstractState,
    ConversationStateMachine,
    MessageFSMConversation,
    StateMachine,
    StateTransition
};

class OrderFSMConversation extends MessageFSMConversation
{
    public const string ORDER_TOKEN    = '%d_order';
    public const string QUANTITY_TOKEN = '%d_quantity';
    public const string ADDRESS_TOKEN  = '%d_address';

    protected function createStateMachine(): StateMachine
    {
        $machine = new ConversationStateMachine(
            'order_conversation',
            $this->userId,
            $this->chatId
        );

        $selectProductState  = new SelectProductState('select_product', 'Select product');
        $selectQuantityState = new SelectQuantityState('select_quantity', 'Select quantity');
        $enterAddressState   = new EnterAddressState('enter_address', 'Enter address');
        $confirmOrderState   = new ConfirmOrderState('confirm_order', 'Confirm order');
        $paymentState        = new PaymentState('payment', 'Payment');
        $completeState       = new OrderCompleteState('complete', 'Order complete');
        $cancelState         = new OrderCancelState('cancel', 'Order cancelled');

        foreach ([$selectProductState, $selectQuantityState, $enterAddressState, $confirmOrderState, $paymentState, $completeState, $cancelState] as $state) {
            $state->setConversation($this);
            $machine->addState($state);
        }

        $completeState->setTerminal(true);
        $cancelState->setTerminal(true);

        $machine->setInitialState('select_product');

        return $machine;
    }

    protected function onComplete(): void
    {
        $this->db()->delete(self::orderToken($this->userId));
        $this->db()->delete(self::quantityToken($this->userId));
        $this->db()->delete(self::addressToken($this->userId));
    }

    public static function orderToken(int $userId): string
    {
        return sprintf(self::ORDER_TOKEN, $userId);
    }

    public static function quantityToken(int $userId): string
    {
        return sprintf(self::QUANTITY_TOKEN, $userId);
    }

    public static function addressToken(int $userId): string
    {
        return sprintf(self::ADDRESS_TOKEN, $userId);
    }
}

class SelectProductState extends AbstractState
{
    private const array PRODUCTS = [
        'pizza'  => ['name' => 'Pizza', 'price' => 15.99],
        'burger' => ['name' => 'Burger', 'price' => 12.50],
        'salad'  => ['name' => 'Salad', 'price' => 8.99],
    ];

    public function __construct(string $id, string $name)
    {
        parent::__construct($id, $name, 'Select a product', 'I want {w:product}');
    }

    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $message      = "Available products:\n";

        foreach (self::PRODUCTS as $key => $product) {
            $message .= "- {$product['name']} (\${$product['price']})\n";
        }

        $message .= "\nWhat would you like to order? (or 'cancel' to cancel)";

        $conversation->api()->sendMessage($ctx->getChatId(), $message);
    }

    public function process(Context $ctx): StateTransition
    {
        $conversation = $this->getConversation();
        $product      = strtolower($conversation->param('product', ''));

        if ($product === 'cancel') {
            return StateTransition::to('cancel');
        }

        if (!isset(self::PRODUCTS[$product])) {
            $conversation->api()->sendMessage(
                $ctx->getChatId(),
                'Invalid product. Please try again.'
            );
            return StateTransition::to('select_product');
        }

        $conversation->db()->save(
            OrderFSMConversation::orderToken($ctx->getUserId()),
            $product
        );

        return StateTransition::to('select_quantity');
    }
}

class SelectQuantityState extends AbstractState
{
    public function __construct(string $id, string $name)
    {
        parent::__construct($id, $name, 'Select quantity', 'I want {d:quantity}');
    }

    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $conversation->api()->sendMessage($ctx->getChatId(), 'How many would you like?');
    }

    public function process(Context $ctx): StateTransition
    {
        $conversation = $this->getConversation();
        $quantity     = (int) $conversation->param('quantity', 0);

        if ($quantity <= 0 || $quantity > 10) {
            $conversation->api()->sendMessage(
                $ctx->getChatId(),
                'Invalid quantity. Please enter a number between 1 and 10.'
            );
            return StateTransition::to('select_quantity');
        }

        $conversation->db()->save(
            OrderFSMConversation::quantityToken($ctx->getUserId()),
            $quantity
        );

        return StateTransition::to('enter_address');
    }
}

class EnterAddressState extends AbstractState
{
    public function __construct(string $id, string $name)
    {
        parent::__construct($id, $name, 'Enter delivery address', 'My address is {all:address}');
    }

    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $conversation->api()->sendMessage($ctx->getChatId(), 'Please enter your delivery address:');
    }

    public function process(Context $ctx): StateTransition
    {
        $conversation = $this->getConversation();
        $address      = trim($conversation->param('address', ''));

        if (empty($address) || strlen($address) < 10) {
            $conversation->api()->sendMessage(
                $ctx->getChatId(),
                'Please enter a valid address (minimum 10 characters).'
            );
            return StateTransition::to('enter_address');
        }

        $conversation->db()->save(
            OrderFSMConversation::addressToken($ctx->getUserId()),
            $address
        );

        return StateTransition::to('confirm_order');
    }
}

class ConfirmOrderState extends AbstractState
{
    public function __construct(string $id, string $name)
    {
        parent::__construct($id, $name, 'Confirm order', '{w:confirmation}');
    }

    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $product      = $conversation->db()->get(OrderFSMConversation::orderToken($ctx->getUserId()));
        $quantity     = (int) $conversation->db()->get(OrderFSMConversation::quantityToken($ctx->getUserId()));
        $address      = $conversation->db()->get(OrderFSMConversation::addressToken($ctx->getUserId()));

        $message = "Order Summary:\n";
        $message .= "Product: {$product}\n";
        $message .= "Quantity: {$quantity}\n";
        $message .= "Address: {$address}\n\n";
        $message .= "Confirm order? (yes/no)";

        $conversation->api()->sendMessage($ctx->getChatId(), $message);
    }

    public function process(Context $ctx): StateTransition
    {
        $conversation = $this->getConversation();
        $confirmation = strtolower($conversation->param('confirmation', ''));

        if (in_array($confirmation, ['yes', 'y', 'confirm'])) {
            return StateTransition::to('payment');
        }

        if (in_array($confirmation, ['no', 'n', 'cancel'])) {
            return StateTransition::to('cancel');
        }

        return StateTransition::to('confirm_order');
    }
}

class PaymentState extends AbstractState
{
    public function __construct(string $id, string $name)
    {
        parent::__construct($id, $name, 'Payment processing', '{w:payment}');
    }

    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $conversation->api()->sendMessage(
            $ctx->getChatId(),
            'Processing payment... Type "paid" when payment is complete.'
        );
    }

    public function process(Context $ctx): StateTransition
    {
        $conversation = $this->getConversation();
        $payment      = strtolower($conversation->param('payment', ''));

        if ($payment === 'paid') {
            return StateTransition::to('complete');
        }

        return StateTransition::to('payment');
    }
}

class OrderCompleteState extends AbstractState
{
    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $conversation->api()->sendMessage(
            $ctx->getChatId(),
            '✅ Order completed successfully! Your order will be delivered soon.'
        );
    }

    public function process(Context $ctx): StateTransition
    {
        return StateTransition::to('complete');
    }
}

class OrderCancelState extends AbstractState
{
    public function onEnter(Context $ctx): void
    {
        $conversation = $this->getConversation();
        $conversation->api()->sendMessage(
            $ctx->getChatId(),
            '❌ Order cancelled. Thank you for using our service!'
        );
    }

    public function process(Context $ctx): StateTransition
    {
        return StateTransition::to('cancel');
    }
}
