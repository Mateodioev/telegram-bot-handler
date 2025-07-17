<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler\Conversations\FSM;

use Mateodioev\TgHandler\Db\DbInterface;
use Psr\Log\{LoggerInterface, NullLogger};

class StatePersistenceManager
{
    private const string STATE_KEY_PREFIX = 'fsm_state_';
    private const string CONTEXT_KEY_PREFIX = 'fsm_context_';

    public function __construct(
        private readonly DbInterface $db,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function saveStateMachine(StateMachine $stateMachine): void
    {
        $stateData = [
            'id' => $stateMachine->getId(),
            'userId' => $stateMachine->getUserId(),
            'chatId' => $stateMachine->getChatId(),
            'currentState' => $stateMachine->getCurrentState()?->getId(),
            'initialState' => $stateMachine->getInitialState()?->getId(),
            'timestamp' => time(),
        ];

        $key = $this->getStateKey($stateMachine->getId(), $stateMachine->getUserId(), $stateMachine->getChatId());
        $this->db->save($key, json_encode($stateData));

        $this->logger->debug('FSM state saved', [
            'machine_id' => $stateMachine->getId(),
            'current_state' => $stateMachine->getCurrentState()?->getId()
        ]);
    }

    public function loadStateMachine(string $machineId, int $userId, int $chatId): ?array
    {
        $key = $this->getStateKey($machineId, $userId, $chatId);
        $data = $this->db->get($key);

        if ($data === null) {
            return null;
        }

        $stateData = json_decode($data, true);

        if (!is_array($stateData)) {
            $this->logger->warning('Invalid FSM state data', ['key' => $key]);
            return null;
        }

        $this->logger->debug('FSM state loaded', [
            'machine_id' => $machineId,
            'current_state' => $stateData['currentState'] ?? null
        ]);

        return $stateData;
    }

    public function deleteStateMachine(string $machineId, int $userId, int $chatId): void
    {
        $stateKey = $this->getStateKey($machineId, $userId, $chatId);
        $contextKey = $this->getContextKey($machineId, $userId, $chatId);

        $this->db->delete($stateKey);
        $this->db->delete($contextKey);

        $this->logger->debug('FSM state deleted', ['machine_id' => $machineId]);
    }

    public function saveContext(string $machineId, int $userId, int $chatId, array $context): void
    {
        $key = $this->getContextKey($machineId, $userId, $chatId);
        $this->db->save($key, json_encode($context));

        $this->logger->debug('FSM context saved', ['machine_id' => $machineId]);
    }

    public function loadContext(string $machineId, int $userId, int $chatId): array
    {
        $key = $this->getContextKey($machineId, $userId, $chatId);
        $data = $this->db->get($key);

        if ($data === null) {
            return [];
        }

        $context = json_decode($data, true);

        if (!is_array($context)) {
            $this->logger->warning('Invalid FSM context data', ['key' => $key]);
            return [];
        }

        return $context;
    }

    public function cleanupExpiredStates(int $maxAge = 86400): int
    {
        $cleaned = 0;
        $cutoff = time() - $maxAge;

        foreach ($this->getAllStateKeys() as $key) {
            $data = $this->db->get($key);
            if ($data === null) {
                continue;
            }

            $stateData = json_decode($data, true);
            if (!is_array($stateData) || !isset($stateData['timestamp'])) {
                continue;
            }

            if ($stateData['timestamp'] < $cutoff) {
                $this->db->delete($key);
                $this->db->delete(str_replace(self::STATE_KEY_PREFIX, self::CONTEXT_KEY_PREFIX, $key));
                $cleaned++;
            }
        }

        $this->logger->info('Cleaned up expired FSM states', ['count' => $cleaned]);

        return $cleaned;
    }

    private function getStateKey(string $machineId, int $userId, int $chatId): string
    {
        return self::STATE_KEY_PREFIX . "{$machineId}_{$userId}_{$chatId}";
    }

    private function getContextKey(string $machineId, int $userId, int $chatId): string
    {
        return self::CONTEXT_KEY_PREFIX . "{$machineId}_{$userId}_{$chatId}";
    }

    private function getAllStateKeys(): array
    {
        return [];
    }
}
