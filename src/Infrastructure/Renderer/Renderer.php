<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Infrastructure\Renderer;

interface Renderer
{
    public function render(string $name): string;
}
