<?php

declare(strict_types=1);

namespace Cekta\DI\Loader;

use Cekta\DI\LoaderInterface;
use Psr\Container\ContainerInterface;

class Factory implements LoaderInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array<string>
     */
    private $dependencies;

    /**
     * @param string $name
     * @param array<string> $dependencies
     */
    public function __construct(string $name, array $dependencies)
    {
        $this->name = $name;
        $this->dependencies = $dependencies;
    }

    public function __invoke(ContainerInterface $container)
    {
        $args = [];
        foreach ($this->dependencies as $dependecy) {
            $args[] = $container->get($dependecy);
        }
        return new $this->name(...$args);
    }
}
