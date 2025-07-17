# Finite State Machine (FSM) Conversations

The FSM conversation system provides a more structured and maintainable approach to handling multi-step conversations compared to the traditional chaining approach.

## Benefits of FSM Conversations

1. **Clear State Management**: Each state has a specific purpose and transitions are explicit
2. **Better Validation**: States can validate input and conditions before transitioning
3. **Persistence**: State can be saved and restored across bot restarts
4. **Debugging**: Easy to track conversation flow and identify issues
5. **Extensibility**: Easy to add new states and transitions

## Core Components

### StateMachine Interface
Defines the contract for managing states and transitions:
- `getCurrentState()`: Get the current state
- `transition(stateId, context)`: Transition to a new state
- `canTransition(stateId, context)`: Check if transition is allowed
- `isComplete()`: Check if conversation is finished

### State Interface
Defines individual states in the conversation:
- `getId()`: Unique state identifier
- `process(context)`: Process user input and return next transition
- `onEnter(context)`: Called when entering the state
- `onExit(context)`: Called when leaving the state
- `isTerminal()`: Whether this state ends the conversation

### StateTransition Class
Represents a transition between states:
- `StateTransition::to(stateId)`: Simple transition
- `StateTransition::conditionalTo(stateId, condition, guard)`: Conditional transition
- `StateTransition::actionTo(stateId, action)`: Transition with action

## Creating FSM Conversations

### Basic Example

```php
class NameFSMConversation extends MessageFSMConversation
{
    protected function createStateMachine(): StateMachine
    {
        $machine = new ConversationStateMachine(
            'name_conversation',
            $this->userId,
            $this->chatId
        );
        
        $nameState = new NameState('name', 'Ask for name');
        $ageState = new AgeState('age', 'Ask for age');
        $completeState = new CompleteState('complete', 'Complete');
        
        $nameState->setConversation($this);
        $ageState->setConversation($this);
        $completeState->setConversation($this)->setTerminal(true);
        
        $machine->addState($nameState);
        $machine->addState($ageState);
        $machine->addState($completeState);
        
        $machine->setInitialState('name');
        
        return $machine;
    }
}
```

### Creating States

```php
class NameState extends AbstractState
{
    public function __construct(string $id, string $name)
    {
        parent::__construct($id, $name, 'Enter name', 'My name is {w:name}');
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
        
        // Save name to database
        $conversation->db()->save("name_{$ctx->getUserId()}", $name);
        
        return StateTransition::to('age');
    }
}
```

### Advanced State Features

#### Conditional Transitions
```php
public function process(Context $ctx): StateTransition
{
    $answer = strtolower($this->conversation->param('answer'));
    
    if (in_array($answer, ['yes', 'y'])) {
        return StateTransition::to('complete');
    }
    
    return StateTransition::to('retry');
}
```

#### Input Validation
```php
public function process(Context $ctx): StateTransition
{
    $age = (int) $this->conversation->param('age');
    
    if ($age < 1 || $age > 150) {
        $this->conversation->api()->sendMessage(
            $ctx->getChatId(),
            'Please enter a valid age between 1 and 150'
        );
        return StateTransition::to('age'); // Stay in same state
    }
    
    return StateTransition::to('confirm');
}
```

#### State Guards
```php
public function canEnter(Context $ctx): bool
{
    // Only allow if user has required permission
    return $ctx->getUser()->isAdmin();
}
```

## State Persistence

The FSM system automatically saves and loads state:

```php
public function execute(array $args = []): ?Conversation
{
    $this->stateMachine->loadState($this->db());
    
    // ... process state ...
    
    $this->stateMachine->saveState($this->db());
    
    return $nextConversation;
}
```

## Usage Examples

### Registration Flow
```php
// States: welcome -> name -> email -> confirm -> complete
$machine->setInitialState('welcome');
```

### Order Process
```php
// States: product -> quantity -> address -> payment -> complete
// With cancel state accessible from any step
```

### Survey System
```php
// States: question1 -> question2 -> question3 -> summary -> complete
// With conditional branching based on answers
```

## Best Practices

1. **Keep States Focused**: Each state should handle one specific task
2. **Use Descriptive Names**: State IDs should clearly indicate their purpose
3. **Validate Input**: Always validate user input before transitioning
4. **Handle Errors Gracefully**: Provide feedback for invalid input
5. **Use Terminal States**: Mark final states as terminal
6. **Clean Up Data**: Remove stored data when conversation completes
7. **Set TTL**: Configure appropriate timeouts for states

## Migration from Traditional Conversations

Traditional chaining approach:
```php
class NameConversation extends MessageConversation
{
    public function execute(array $args = [])
    {
        $name = $this->param('name');
        $this->db()->save("name_{$this->userId}", $name);
        
        return AgeConversation::fromContext($this->ctx());
    }
}
```

FSM approach:
```php
class NameFSMConversation extends MessageFSMConversation
{
    protected function createStateMachine(): StateMachine
    {
        // Define all states and transitions upfront
        // Better organization and flow control
    }
}
```

## Testing FSM Conversations

The FSM system includes comprehensive test coverage:

```php
public function testStateTransition()
{
    $machine = new ConversationStateMachine('test', 123, 456);
    $machine->addState(new TestState('state1'));
    $machine->addState(new TestState('state2'));
    
    $machine->setInitialState('state1');
    $machine->transition('state2', $context);
    
    $this->assertEquals('state2', $machine->getCurrentState()->getId());
}
```

This FSM system provides a robust foundation for complex conversation flows while maintaining simplicity for basic use cases.