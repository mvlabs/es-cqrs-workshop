<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Infrastructure\Projector;

use MVLabs\EsCqrsWorkshop\Domain\DomainEvent\PizzeriaCreated;

final class RecordPizzeriaOnPizzeriaCreated
{
    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(PizzeriaCreated $pizzeriaCreated): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO pizzerias (id, name) VALUES (:id, :name)'
        );

        $statement->execute([
            'id' => (string) $pizzeriaCreated->pizzeriaId(),
            'name' => $pizzeriaCreated->name()
        ]);
    }
}
