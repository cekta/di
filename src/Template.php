<?php

declare(strict_types=1);

namespace Cekta\DI;

class Template
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param array<string, mixed> $params
     * @return string|false
     */
    public function render(array $params): string|false
    {
        extract($params);
        ob_start();
        include $this->path;
        return ob_get_clean();
    }
}
