<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Psr\Container\ContainerInterface;

class InfiniteRecursionDetector implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var string[]
     */
    private $calls = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get($id)
    {
        if (in_array($id, $this->calls)) {
            throw new InfiniteRecursion($id, $this->calls);
        }
        $this->calls[] = $id;
        $result = $this->container->get($id);
        array_pop($this->calls);
        return $result;
    }

    public function has($id): bool
    {
        return $this->container->has($id);
    }
}
