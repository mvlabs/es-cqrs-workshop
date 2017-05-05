<?php

declare(strict_types=1);

namespace MVLabs\EsCqrsWorkshop\Infrastructure\Renderer;

final class HtmlRenderer implements Renderer
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $templatesPath)
    {
        $this->path = $templatesPath;
    }

    public function render(string $name): string
    {
        return file_get_contents($this->path . $name . '.html');
    }
}
