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

    private function __construct(string $customerName, string $pizzaTaste)
    {
        $this->customerName = $customerName;
        $this->pizzaTaste = $pizzaTaste;
    }

    public static function fromCustomerNameAndPizzaTaste(string $customerName, string $pizzaTaste): self
    {
        return new self($customerName, $pizzaTaste);
    }
}
