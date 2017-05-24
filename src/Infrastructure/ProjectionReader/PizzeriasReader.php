<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Infrastructure\ProjectionReader;

use MVLabs\EsCqrsWorkshop\Domain\ProjectionReader\PizzeriasReaderInterface;

final class PizzeriasReader implements PizzeriasReaderInterface
{
    /**
     * @var \PDO
     */
    private $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function listPizzerias(): array
    {
        $statement = $this->connection->query('SELECT id, name from pizzerias');

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
