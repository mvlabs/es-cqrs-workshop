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

    public function listOrders(): array
    {
        $statement = $this->connection->query('SELECT id, name, pizzas FROM pizzerias');

        $orders = [];

        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $pizzeria) {
            $pizzeriaOrders = json_decode($pizzeria['pizzas']);

            foreach ($pizzeriaOrders as $order) {
                $orders[] = [
                    'pizzeria' => $pizzeria['id'],
                    'name' => $pizzeria['name'],
                    'customer' => $order->customer,
                    'pizza' => $order->pizza,
                    'at' => $order->at
                ];
            }
        }

        return $orders;
    }
}
