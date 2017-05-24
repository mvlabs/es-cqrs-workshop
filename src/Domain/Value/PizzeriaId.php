<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\Value;

use Ramsey\Uuid\Uuid;

final class PizzeriaId
{
    /**
     * @var Uuid
     */
    private $id;

    private function __construct()
    {
    }

    public static function new(): self
    {
        $instance = new self();

        $instance->id = Uuid::uuid4();

        return $instance;
    }

    public static function fromString(string $pizzeriaId): self
    {
        $instance = new self();

        $instance->id = Uuid::fromString($pizzeriaId);

        return $instance;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
