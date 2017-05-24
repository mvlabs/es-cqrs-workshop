<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\Command;

use DateTimeImmutable;
use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;
use Prooph\Common\Messaging\Command;

final class CompleteOrder extends Command
{
    /**
     * @var string
     */
    private $customerName;

    /**
     * @var string
     */
    private $pizzeriaId;

    /**
     * @var string
     */
    private $pizzaTaste;

    /**
     * @var \DateTimeImmutable
     */
    private $orderCreatedAt;

    private function __construct(
        string $customerName,
        string $pizzeriaId,
        string $pizzaTaste,
        int $timestamp
    ) {
        $this->init();

        $this->customerName = $customerName;
        $this->pizzeriaId = $pizzeriaId;
        $this->pizzaTaste = $pizzaTaste;
        $this->orderCreatedAt = date_create_immutable_from_format('U', (string) $timestamp);
    }

    public static function fromCustomerNamePizzeriaPizzaTasteandTimestamp(
        string $customerName,
        string $pizzeriaId,
        string $pizzaTaste,
        int $timestamp
    ): self
    {
        return new self($customerName, $pizzeriaId, $pizzaTaste, $timestamp);
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function pizzaTaste(): string
    {
        return $this->pizzaTaste;
    }

    public function pizzeriaId(): PizzeriaId
    {
        return PizzeriaId::fromString($this->pizzeriaId);
    }

    public function orderCreatedAt(): DateTimeImmutable
    {
        return $this->orderCreatedAt;
    }

    public function payload(): array
    {
        return [
            'customerName' => $this->customerName,
            'pizzeriaId' => $this->pizzeriaId,
            'pizzaTaste' => $this->pizzaTaste,
            'orderCreatedAt' => $this->orderCreatedAt->format('U')
        ];
    }

    public function setPayload(array $payload): void
    {
        $this->customerName = (string) $payload['customerName'];
        $this->pizzeriaId = (string) $payload['pizzeriaId'];
        $this->pizzaTaste = (string) $payload['pizzaTaste'];
        $this->orderCreatedAt = date_create_immutable_from_format('U', $payload['orderCreatedAt']);
    }
}
