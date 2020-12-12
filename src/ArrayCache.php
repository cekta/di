<?php

declare(strict_types=1);

namespace Cekta\DI;

use Psr\Container\ContainerInterface;

class ArrayCache implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var string[]
     */
    private $values = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function get($id)
    {
        if (!array_key_exists($id, $this->values)) {
            $this->values[$id] = $this->container->get($id);
        }
        return $this->values[$id];
    }

    public function has($id): bool
    {
        return array_key_exists($id, $this->values) || $this->container->has($id);
    }
}
