<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\Command;

use MVLabs\EsCqrsWorkshop\Domain\Value\PizzeriaId;
use Prooph\Common\Messaging\Command;

final class NotifyDeliveryBoy extends Command
{
    /**
     * @var string
     */
    private $customerName;

    /**
     * @var PizzeriaId
     */
    private $pizzeriaId;

    /**
     * @var string
     */
    private $pizza;

    private function __construct(
        string $customerName,
        PizzeriaId $pizzeriaId,
        string $pizza
    ) {
        $this->init();

        $this->customerName = $customerName;
        $this->pizzeriaId = $pizzeriaId;
        $this->pizza = $pizza;
    }

    public static function fromCustomerPizzeriaAndPizza(
        string $customerName,
        PizzeriaId $pizzeriaId,
        string $pizza
    ): self
    {
        return new self($customerName, $pizzeriaId, $pizza);
    }

    public function payload(): array
    {
        return [
            'customerName' => $this->customerName,
            'pizzeriaId' => $this->pizzeriaId,
            'pizza' => $this->pizza,
        ];
    }

    public function setPayload(array $payload): void
    {
        $this->customerName = (string) $payload['customerName'];
        $this->pizzeriaId = (string) $payload['pizzeriaId'];
        $this->pizza = (string) $payload['pizza'];
    }
}
