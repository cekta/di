<?php

declare(strict_types=1);

namespace Cekta\DI\Strategy;

use Cekta\DI\Exception\NotFound;
use Cekta\DI\Reflection;
use Cekta\DI\Strategy\Definition\Factory;
use Psr\Container\ContainerInterface;

class Autowiring implements ContainerInterface
{
    /**
     * @var Reflection
     */
    private $reflection;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(Reflection $reflection, ContainerInterface $container)
    {
        $this->reflection = $reflection;
        $this->container = $container;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFound($id);
        }
        return (new Factory($id, ...$this->reflection->getDependencies($id)))($this->container);
    }

    public function has($id): bool
    {
        return $this->reflection->isInstantiable($id);
    }
}
