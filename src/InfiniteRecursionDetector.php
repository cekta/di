<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Psr\Container\ContainerInterface;

class InfiniteRecursionDetector implements ContainerInterface
{
    private ContainerInterface $container;
    /**
     * @var string[]
     */
    private array $calls = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(string $id)
    {
        if (in_array($id, $this->calls)) {
            throw new InfiniteRecursion($id, $this->calls);
        }
        $this->calls[] = $id;
        $result = $this->container->get($id);
        array_pop($this->calls);
        return $result;
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}
