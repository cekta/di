<?php

namespace Cekta\DI\Test;

use Psr\Container\ContainerInterface;

class ContainerCompiled implements ContainerInterface
{
    /**
     * @var array<string, mixed>
     */
    public array $params;

    /**
     * @param array<string, mixed> $params
     */
    public function __construct(array $params)
    {
        $this->params = $params;
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
