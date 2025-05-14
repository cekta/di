<?php

declare(strict_types=1);

namespace Cekta\DI;

use RuntimeException;

class Template
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param array<string, mixed> $params
     * @return string
     */
    public function render(array $params): string
    {
        extract($params);
        ob_start();
        include $this->path;
        return $this->handleResult(ob_get_clean());
    }

    /**
     * @param string|false $result
     * @return string
     */
    private function handleResult(string|false $result): string
    {
        if ($result === false) {
            throw new RuntimeException("ob_get_clean return false for {$this->path}");
        }
        return $result;
    }
}
