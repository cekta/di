<?php

namespace Cekta\DI\Test;

use Psr\Container\ContainerInterface;

class ContainerDevelop implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    public array $params;
    /**
     * @var array<string, string>
     */
    public array $interfaces;
    /**
     * @var array<string, mixed>
     */
    public array $definitions;

    /**
     * @param array<string, mixed> $params
     * @param array<string, string> $interfaces
     * @param array<string, mixed> $definitions
     */
    public function __construct(array $params, array $interfaces, array $definitions)
    {
        $this->params = $params;
        $this->interfaces = $interfaces;
        $this->definitions = $definitions;
    }

    public function get($id)
    {
        return 'something';
    }

    public function has($id): bool
    {
        return false;
    }
}
