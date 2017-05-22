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
        $statement = $this->connection->prepare(
            'UPDATE pizzerias SET pizzas = (' .
            '   SELECT to_jsonb(array_agg(ord)) FROM (' .
            '       SELECT ord FROM	(' .
            '           SELECT jsonb_array_elements(pizzas) AS ord FROM pizzerias) AS orders '.
            '           WHERE ord != jsonb_build_object(\'customer\', (:customer)::text, \'at\', (:at)::int, \'pizza\', (:pizza)::text) '.
            '       ) AS something '.
            '   )' .
            'WHERE id = :id'
        );

        $statement->execute([
            'id' => (string) $orderCompleted->pizzeriaId(),
            'pizza' => $orderCompleted->pizzaTaste(),
            'customer' => $orderCompleted->customerName(),
            'at' => $orderCompleted->orderCreatedAt()->format('U')
        ]);
    }
}
