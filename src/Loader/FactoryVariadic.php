<?php

declare(strict_types=1);

namespace Cekta\DI\Loader;

use Cekta\DI\LoaderInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class FactoryVariadic implements LoaderInterface
{
    /**
     * @var string
     */
    private $className;
    /**
     * @var array<string>
     */
    private $dependencies;

    public function __construct(string $className, string ...$dependencies)
    {
        $this->className = $className;
        $this->dependencies = $dependencies;
    }

    public function __invoke(ContainerInterface $container)
    {
        $args = [];
        foreach ($this->dependencies as $dependecy) {
            $args[] = $container->get($dependecy);
        }
        if (count($args) > 0) {
            $variadic = array_pop($args);
            if (!is_array($variadic)) {
                throw new InvalidArgumentException('must be array');
            }
            $args = array_merge($args, $variadic);
        }
        return new $this->className(...$args);
    }
}
