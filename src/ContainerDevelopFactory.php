<?php

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

/**
 * @external
 */
class ContainerDevelopFactory
{
    private string $class;

    public function __construct(string $class = Container::class)
    {
        $this->class = $class;
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, string> $interfaces
     * @param array<string, mixed> $definitions
     * @return ContainerInterface
     */
    public function make(array $params, array $interfaces, array $definitions): ContainerInterface
    {
        $result = new $this->class($params, $interfaces, $definitions);
        if (!$result instanceof ContainerInterface) {
            throw new \InvalidArgumentException('make must return instance of ContainerInterface');
        }
        return $result;
    }
}
