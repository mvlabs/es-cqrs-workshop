<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Infrastructure\Projector;

use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\OrderCompleted;

final class RecordPizzeriaOnOrderCompleted
{
    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(OrderCompleted $orderCompleted): void
    {
        $statement = $this->connection->prepare('');

        $statement->execute([
            'id' => (string)$orderCompleted->pizzeriaId(),
            'pizza' => $orderCompleted->pizzaTaste(),
            'customer' => $orderCompleted->customerName()
        ]);
    }
}
