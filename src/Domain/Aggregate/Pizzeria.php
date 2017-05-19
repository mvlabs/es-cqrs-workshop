<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\Aggregate;

use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\OrderCompleted;
use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\PizzeriaCreated;
use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\OrderReceived;
use MVLabs\EsCqrsWorkshop\Domain\Value\Order;
use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;
use Webmozart\Assert\Assert;

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

    /**
     * @var Order[]
     */
    private $orders;

    public static function new($name): self
    {
        Assert::notEmpty($name, 'The name of the pizzeria must be not empty');

        $instance = new self();

        $instance->recordThat(PizzeriaCreated::fromIdAndName(
            PizzeriaId::new(),
            $name
        ));

        return $instance;
    }

    public function addOrder(string $customerName, string $pizzaTaste): void
    {
        Assert::notEmpty($customerName, 'The name of the customer must be not empty');
        Assert::notEmpty($pizzaTaste, 'The name of the pizza must be not empty');

        $this->recordThat(OrderReceived::fromCustomerPizzeriaAndPizzaTaste(
            $customerName,
            $this->id,
            $pizzaTaste
        ));
    }

    public function completeOrder($customerName, $pizzaTaste)
    {
        // TODO: check order is present

        $this->recordThat(OrderCompleted::fromCustomerPizzeriaAndPizzaTaste(
            $customerName,
            $this->id,
            $pizzaTaste
        ));
    }

    public function whenPizzeriaCreated(PizzeriaCreated $pizzeriaCreated): void
    {
        $this->id = $pizzeriaCreated->pizzeriaId();
        $this->name = $pizzeriaCreated->name();
    }

    public function whenOrderReceived(OrderReceived $orderReceived): void
    {
        $this->orders[] = Order::fromCustomerNameAndPizzaTaste(
            $orderReceived->customerName(),
            $orderReceived->pizzaTaste()
        );
    }

    public function id(): PizzeriaId
    {
        return $this->id;
    }

    public function aggregateId(): string
    {
        return (string) $this->id;
    }

    public function apply(AggregateChanged $event): void
    {
        $handler = $this->determineEventHandlerMethodFor($event);

        if (!method_exists($this, $handler)) {
            throw new \RuntimeException(sprintf(
                'Missing event handler method %s for aggregate root %s',
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
