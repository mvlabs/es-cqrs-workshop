<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\Value;

final class Order
{
    /**
     * @var string
     */
    private $customerName;

    /**
     * @var string
     */
    private $pizzaTaste;

    /**
     * @var \DateTimeImmutable
     */
    private $createdAt;

    private function __construct(
        string $customerName,
        string $pizzaTaste,
        \DateTimeImmutable $createdAt
    ) {
        $this->customerName = $customerName;
        $this->pizzaTaste = $pizzaTaste;
        $this->createdAt = $createdAt;
    }

    public static function fromCustomerNamePizzaTasteAndDateTime(
        string $customerName,
        string $pizzaTaste,
        \DateTimeImmutable $createdAt
    ): self
    {
        return new self($customerName, $pizzaTaste, $createdAt);
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function pizzaTaste(): string
    {
        return $this->pizzaTaste;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
