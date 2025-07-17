<?php

declare(strict_types=1);

namespace Mateodioev\TgHandler;

use Mateodioev\TgHandler\Events\{
    EventInterface,
    EventType
};

use function array_keys;
use function count;
use function spl_object_id;

/**
 * Class to store events.
 * @internal
 */
final class EventStorage
{
    /**
     * @var array<EventType, int[]>
     */
    private array $events = [];

    /**
     * @var array<int, EventInterface>
     */
    private array $eventsPointers = [];

    /**
     * @var array<int, string> Map of event ID to event type name for O(1) deletion
     */
    private array $eventIdToType = [];

    /**
     * Resolve all events
     * @return array<EventType, EventInterface[]>
     */
    public function all(): array
    {
        $events = [];

        foreach ($this->events as $type => $eventIds) {
            $events[$type] = [];
            foreach ($eventIds as $eventId) {
                $events[$type][] = $this->eventsPointers[$eventId] ?? null;
            }
        }
        return $events;
    }

    /**
     * Get total events count. If $eventType is specified, return count of events with this type.
     */
    public function total(?EventType $eventType = null): int
    {
        if ($eventType === null) {
            return count($this->eventsPointers);
        }

        return count($this->events[$eventType->name()] ?? []);
    }

    /**
     * Get event by id.
     */
    public function get(int $id): ?EventInterface
    {
        return $this->eventsPointers[$id] ?? null;
    }

    /**
     * Get all events with given type.
     * @return EventInterface[]
     */
    public function resolve(EventType $eventType): array
    {
        $events = [];
        foreach (($this->events[$eventType->name()] ?? []) as $eventId) {
            $event = $this->eventsPointers[$eventId] ?? null;
            if ($event === null) {
                continue;
            }

            $events[] = $event;
        }
        return $events;
    }

    /**
     * Add new event to storage.
     * @return int Event id.
     */
    public function add(EventInterface $event): int
    {
        $eventId = $this->getEventId($event);

        if ($this->exitsEventId($eventId)) {
            return $eventId;
        }

        $eventTypeName = $event->type()->name();
        $this->eventsPointers[$eventId] = $event;
        $this->events[$eventTypeName][] = $eventId;
        $this->eventIdToType[$eventId] = $eventTypeName;

        return $eventId;
    }

    /**
     * Delete event from storage.
     */
    public function delete(EventInterface $event): bool
    {
        return $this->deleteById($this->getEventId($event));
    }

    public function deleteById(int $eventId): bool
    {
        if (!$this->exitsEventId($eventId)) {
            return false;
        }

        $eventTypeName = $this->eventIdToType[$eventId];

        unset($this->eventsPointers[$eventId]);
        unset($this->eventIdToType[$eventId]);

        // Remove from events array - use array_filter for better performance than foreach
        $this->events[$eventTypeName] = array_filter(
            $this->events[$eventTypeName],
            fn ($id) => $id !== $eventId
        );

        return true;
    }

    public function clearType(EventType $type): EventStorage
    {
        $typeName = $type->name();
        $eventIds = $this->events[$typeName] ?? [];

        // Clear events array for this type
        $this->events[$typeName] = [];

        // Remove all events of this type from pointers and type mapping
        foreach ($eventIds as $eventId) {
            unset($this->eventsPointers[$eventId]);
            unset($this->eventIdToType[$eventId]);
        }

        return $this;
    }

    public function clear(): EventStorage
    {
        $this->eventsPointers = [];
        $this->events = [];
        $this->eventIdToType = [];

        return $this;
    }

    /**
     * Return true if event exists in storage.
     */
    public function exists(EventInterface $event): bool
    {
        return $this->exitsEventId($this->getEventId($event));
    }

    public function exitsEventId(int $id): bool
    {
        return isset($this->eventsPointers[$id]);
    }

    /**
     * Get all event types.
     * @return string[]
     */
    public function types(): array
    {
        // receive all events if we have a listener for all events
        if (isset($this->events['all'])) {
            return [];
        }

        $types = array_keys($this->events);
        unset($types['all']);

        return $types;
    }

    /**
     * Get event id.
     */
    private function getEventId(EventInterface $event): int
    {
        return spl_object_id($event);
    }
}
