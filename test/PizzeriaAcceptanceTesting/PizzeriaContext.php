<?php

declare(strict_types=1);

namespace PizzeriaAcceptanceTesting;

use Assert\Assertion;
use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Prooph\EventSourcing\AggregateChanged;

final class PizzeriaContext implements Context
{
    /**
     * @Given I have a pizzeria
     */
    public function iHaveAPizzeria(): void
    {
        throw new PendingException();
    }

    /**
     * @When I order a pizza at the pizzeria
     */
    public function iOrderAPizzaAtThePizzeria(): void
    {
        throw new PendingException();
    }

    /**
     * @Then the pizza should be enlisted on the pizzeria
     */
    public function thePizzaShouldBeEnlistedOnThePizzeria(): void
    {
        throw new PendingException();
    }

    /**
     * @Given I have ordered a pizza at the pizzeria
     */
    public function iHaveOrderedAPizzaAtThePizzeria(): void
    {
        throw new PendingException();
    }

    /**
     * @When the pizza is completed
     */
    public function thePizzaIsCompleted(): void
    {
        throw new PendingException();
    }

    /**
     * @Then the pizza should not be enlisted on the pizzeria
     */
    public function thePizzaShouldNotBeEnlistedOnThePizzeria(): void
    {
        throw new PendingException();
    }

    /**
     * @Then the pizzeria should not be able to complete a pizza
     */
    public function thePizzeriaShouldNotBeAbleToCompleteAPizza(): void
    {
        throw new PendingException();
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
