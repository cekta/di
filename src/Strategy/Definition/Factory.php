<?php

declare(strict_types=1);

namespace Cekta\DI\Strategy\Definition;

use Psr\Container\ContainerInterface;

class Factory
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var string[]
     */
    private $dependencies;

    public function __construct(string $className, string ...$dependencies)
    {
        $this->className = $className;
        $this->dependencies = $dependencies;
    }

    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        $args = [];
        foreach ($this->dependencies as $dependecy) {
            $args[] = $container->get($dependecy);
        }
        return new $this->className(...$args);
    }
}
