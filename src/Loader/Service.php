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

    public static function createObject(string $name, array $dependencies): Service
    {
        return new Service(static function (ContainerInterface $container) use ($dependencies, $name) {
            foreach ($dependencies as $dependecy) {
                $args[] = $container->get($dependecy);
            }
            return new $name(...$args ?? []);
        });
    }

    public function __invoke(ContainerInterface $container)
    {
        return ($this->closure)($container);
    }
}
