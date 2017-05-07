<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain;

use Prooph\Common\Messaging\Command;

final class OrderPizza extends Command
{
    /**
     * @var string
     */
    private $name;

    private function __construct(string $name)
    {
        $this->init();

        $this->name = $name;
    }

    public static function fromName(string $name): self
    {
        return new self($name);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function payload(): array
    {
        return [
            'name' => $this->name
        ];
    }

    public function setPayload(array $payload): void
    {
        $this->name = (string) $payload['name'];
    }
}
