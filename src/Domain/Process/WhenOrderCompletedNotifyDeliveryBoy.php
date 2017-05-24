<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\Process;

use MVLabs\EsCqrsWorkshop\Domain\Command\NotifyDeliveryBoy;
use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\OrderCompleted;
use Prooph\ServiceBus\CommandBus;

final class WhenOrderCompletedNotifyDeliveryBoy
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(OrderCompleted $orderCompleted)
    {
        $this->commandBus->dispatch(NotifyDeliveryBoy::fromCustomerPizzeriaAndPizza(
            $orderCompleted->customerName(),
            $orderCompleted->pizzeriaId(),
            $orderCompleted->pizzaTaste()
        ));
    }
}
