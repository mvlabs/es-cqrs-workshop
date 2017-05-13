<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\Command;

use Prooph\Common\Messaging\Command;

final class AddOrder extends Command
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

    private function __construct(string $customerName, string $pizzeriaId, string $pizzaTaste)
    {
        $this->init();

        $this->customerName = $customerName;
        $this->pizzeriaId = $pizzeriaId;
        $this->pizzaTaste = $pizzaTaste;
    }

    public static function fromCustomerNamePizzeriaAndPizzaTaste(string $customerName, string $pizzeriaId, string $pizzaTaste): self
    {
        return new self($customerName, $pizzeriaId, $pizzaTaste);
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function pizzaTaste(): string
    {
        return $this->pizzaTaste;
    }

    public function pizzeriaId(): string
    {
        return $this->pizzeriaId;
    }

    public function payload(): array
    {
        return [
            'customerName' => $this->customerName,
            'pizzeriaId' => $this->pizzeriaId,
            'pizzaTaste' => $this->pizzaTaste,
        ];
    }

    public function setPayload(array $payload): void
    {
        $this->customerName = (string) $payload['customerName'];
        $this->pizzeriaId = (string) $payload['pizzeriaId'];
        $this->pizzaTaste = (string) $payload['pizzaTaste'];
    }
}
