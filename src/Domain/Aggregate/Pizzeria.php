<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\Aggregate;

use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\PizzeriaCreated;
use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;
use Ramsey\Uuid\Uuid;

final class Pizzeria extends AggregateRoot
{
    /**
     * @var PizzeriaId
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    public static function new($name): self
    {
        $instance = new self();

        $instance->recordThat(PizzeriaCreated::fromIdAndName(
            PizzeriaId::new(),
            $name
        ));

        return $instance;
    }

    public function whenPizzeriaCreated(PizzeriaCreated $pizzeriaCreated): void
    {
        $this->id = $pizzeriaCreated->pizzeriaId();
        $this->name = $pizzeriaCreated->name();
    }

    public function aggregateId(): string
    {
        return $this->id->toString();
    }

    public function apply(AggregateChanged $event): void
    {
        $handler = $this->determineEventHandlerMethodFor($event);

        if (!method_exists($this, $handler)) {
            throw new \RuntimeException(sprintf(
                "Missing event handler method %s for aggregate root %s",
                $handler,
                get_class($this)
            ));
        }

        $this->{$handler}($event);
    }

    private function determineEventHandlerMethodFor(AggregateChanged $event): string
    {
        return 'when' . implode(array_slice(explode('\\', get_class($event)), -1));
    }
}
