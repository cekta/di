<?php

declare(strict_types=1);

namespace Cekta\DI\Loader;

use Cekta\DI\LoaderInterface;
use Closure;
use Psr\Container\ContainerInterface;

class Service implements LoaderInterface
{
    /**
     * @var Closure
     */
    private $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public static function createObject(string $name, array $dependencies)
    {
        $closure = function (ContainerInterface $container) use ($dependencies, $name) {
            $args = [];
            foreach ($dependencies as $dependecy) {
                $args[] = $container->get($dependecy);
            }
            return new $name(...$args);
        };
        return new Service($closure);
    }

    public function __invoke(ContainerInterface $container)
    {
        return ($this->closure)($container);
    }
}
