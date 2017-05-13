<?php

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
        $statement = $this->connection->prepare('SELECT id, name from pizzerias');
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}
