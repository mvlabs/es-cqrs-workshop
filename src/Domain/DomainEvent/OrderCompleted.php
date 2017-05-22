<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\DomainEvent;

use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;
use Prooph\EventSourcing\AggregateChanged;

final class OrderCompleted extends AggregateChanged
{
    public static function fromCustomerPizzeriaPizzaTasteAndDateTime(
        string $customerName,
        PizzeriaId $id,
        string $pizzaTaste,
        \DateTimeImmutable $orderCreatedAt
    ): self
    {
        return self::occur(
            (string) $id,
            [
                'customerName' => $customerName,
                'pizzaTaste' => $pizzaTaste,
                'orderCreatedAt' => $orderCreatedAt->format('U')
            ]
        );
    }

    public function pizzeriaId(): PizzeriaId
    {
        return PizzeriaId::fromString($this->aggregateId());
    }

    public function customerName(): string
    {
        return (string) $this->payload()['customerName'];
    }

    public function pizzaTaste() : string
    {
        return (string) $this->payload()['pizzaTaste'];
    }

    public function orderCreatedAt(): \DateTimeImmutable
    {
        return date_create_immutable_from_format('U', (string) $this->payload()['orderCreatedAt']);
    }
}
