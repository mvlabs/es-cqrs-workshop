<?php

namespace MVLabs\EsCqrsWorkshop\ProjectionReader;

use Doctrine\DBAL\Driver\Connection;
use MVLabs\EsCqrsWorkshop\Domain\ProjectionReader\PizzeriasReaderInterface;

final class PizzeriasReader implements PizzeriasReaderInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function listPizzerias(): array
    {
        return $this->connection->fetchAll(
            'SELECT * from pizzerias'
        );
    }
}
