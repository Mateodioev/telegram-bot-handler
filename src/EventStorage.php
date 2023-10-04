<?php

namespace Mateodioev\TgHandler;

use Mateodioev\TgHandler\Events\{
    EventInterface,
    EventType
};

use function spl_object_id;

/**
 * Class to store events.
 * @internal
 */
final class EventStorage
{
    private array $events = [];

    /**
     * @var array<int, EventInterface>
     */
    private array $eventsPointers = [];

    /**
     * Resolver all events
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
            return \count($this->eventsPointers);
        }

        return \count($this->events[$eventType->name()] ?? []);
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
        $eventsPointers = $this->events[$eventType->name()] ?? [];

        foreach ($eventsPointers as $eventId) {
            $event = $this->get($eventId);
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

        $this->eventsPointers[$eventId]         = $event;
        $this->events[$event->type()->name()][] = $eventId;

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

        $eventType = $this->eventsPointers[$eventId]->type();
        $eventName = $eventType->name();

        unset($this->eventsPointers[$eventId]);

        foreach ($this->events[$eventName] as $i => $id) {
            if ($id === $eventId) {
                unset($this->events[$eventName][$i]);
                break;
            }
        }

        return true;
    }

    public function clearType(EventType $type): static
    {
        $this->events[$type->name()] = [];

        foreach ($this->eventsPointers as $eventId => $event) {
            if ($event->type() === $type) {
                unset($this->eventsPointers[$eventId]);
            }
        }
        return $this;
    }

    public function clear(): static
    {

        $this->eventsPointers = [];
        $this->events         = [];

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
        $types = \array_keys($this->events);
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
