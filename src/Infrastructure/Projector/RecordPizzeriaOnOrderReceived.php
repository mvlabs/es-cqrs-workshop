<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Infrastructure\Projector;

use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\OrderReceived;

final class RecordPizzeriaOnOrderReceived
{
    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(OrderReceived $orderReceived): void
    {
        $statement = $this->connection->prepare(
            'UPDATE pizzerias SET pizzas = pizzas || jsonb_build_object(\'customer\', (:customer)::text, \'pizza\', (:pizza)::text, \'at\', (:at)::int) WHERE id = :id'
        );

        $statement->execute([
            'id' => (string) $orderReceived->pizzeriaId(),
            'pizza' => $orderReceived->pizzaTaste(),
            'customer' => $orderReceived->customerName(),
            'at' => $orderReceived->createdAt()->format('U')
        ]);
    }
}
