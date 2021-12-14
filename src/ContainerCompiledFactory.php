<?php

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

/**
 * @external
 */
class ContainerCompiledFactory
{
    private string $class;

    public function __construct(string $class = '\\App\\Container')
    {
        $this->class = $class;
    }

    /**
     * @param array<string, mixed> $params
     * @return ContainerInterface
     */
    public function make(array $params): ContainerInterface
    {
        $result = new $this->class($params);
        if (!$result instanceof ContainerInterface) {
            throw new \InvalidArgumentException('make must return instance of ContainerInterface');
        }
        return $result;
    }

    public function isClassExist(): bool
    {
        return class_exists($this->class);
    }
}
