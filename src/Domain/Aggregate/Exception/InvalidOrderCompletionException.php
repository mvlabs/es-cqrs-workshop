<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Domain\Aggregate\Exception;

final class InvalidOrderCompletionException extends \InvalidArgumentException
{
    public static function fromInvalidOrder(
        string $pizzeriaName,
        string $customerName,
        string $pizzaTaste
    ) {
        return new self(sprintf(
            'No order for pizza %s by customer %s present for pizzeria %s',
            $pizzaTaste,
            $customerName,
            $pizzeriaName
        ));
    }
}
