<?php

declare(strict_types=1);

namespace PizzeriaAcceptanceTesting;

use Assert\Assertion;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use MVLabs\EsCqrsWorkshop\Domain\Aggregate\Exception\InvalidOrderCompletionException;
use MVLabs\EsCqrsWorkshop\Domain\Aggregate\Pizzeria;
use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\OrderCompleted;
use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\OrderReceived;
use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\PizzeriaCreated;
use Prooph\EventSourcing\AggregateChanged;

final class PizzeriaContext implements Context
{
    /**
     * @var Pizzeria
     */
    private $pizzeria;

    private $orderCreatedAt;

    /**
     * @Given I have a pizzeria
     */
    public function iHaveAPizzeria(): void
    {
        $this->pizzeria = Pizzeria::new('da Gennaro');

        $this->assertEvent(PizzeriaCreated::fromIdAndName(
            $this->pizzeria->id(),
            'da Gennaro'
        ));
    }

    /**
     * @When I order a pizza at the pizzeria
     */
    public function iOrderAPizzaAtThePizzeria(): void
    {
        $this->pizzeria->addOrder('gigi', 'margherita');
    }

    /**
     * @Then the pizza should be enlisted on the pizzeria
     */
    public function thePizzaShouldBeEnlistedOnThePizzeria(): void
    {
        $this->assertEvent(OrderReceived::fromCustomerPizzeriaAndPizzaTaste(
            'gigi',
            $this->pizzeria->id(),
            'margherita'
        ));
    }

    /**
     * @Given I have ordered a pizza at the pizzeria
     */
    public function iHaveOrderedAPizzaAtThePizzeria(): void
    {
        $this->pizzeria->addOrder('gigi', 'margherita');

        $orderReceived = OrderReceived::fromCustomerPizzeriaAndPizzaTaste(
            'gigi',
            $this->pizzeria->id(),
            'margherita'
        );

        $this->orderCreatedAt = $orderReceived->createdAt();

        $this->assertEvent($orderReceived);
    }

    /**
     * @When the pizza is completed
     */
    public function thePizzaIsCompleted(): void
    {
        $this->pizzeria->completeOrder(
            'gigi',
            'margherita',
            $this->orderCreatedAt
        );
    }

    /**
     * @Then the pizza should not be enlisted on the pizzeria
     */
    public function thePizzaShouldNotBeEnlistedOnThePizzeria(): void
    {
        $this->assertEvent(OrderCompleted::fromCustomerPizzeriaPizzaTasteAndDateTime(
            'gigi',
            $this->pizzeria->id(),
            'margherita',
            $this->orderCreatedAt
        ));
    }

    /**
     * @Then the pizzeria should not be able to complete a pizza
     */
    public function thePizzeriaShouldNotBeAbleToCompleteAPizza(): void
    {
        try {
            $this->pizzeria->completeOrder('gigi', 'margherita', date_create_immutable());

            throw new \RuntimeException('An inexistend order was completed');
        } catch (\Exception $e) {
            Assertion::isInstanceOf($e, InvalidOrderCompletionException::class);
        }
    }

    private function assertEvent(AggregateChanged $event): void
    {
        $reflectionPopEvents = new \ReflectionMethod($this->pizzeria, 'popRecordedEvents');
        $reflectionPopEvents->setAccessible(true);
        $events = $reflectionPopEvents->invoke($this->pizzeria);
        Assertion::count($events, 1);
        Assertion::same(get_class($event), get_class($events[0]));
        Assertion::eq($event->payload(), $events[0]->payload());
    }
}
