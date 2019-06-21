<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Exception\NotFound;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;

class Autowire implements ProviderInterface
{
    public function provide(string $name, ContainerInterface $container)
    {
        try {
            $class = new Autowire\ReflectionClass($name);
            $args = [];
            foreach ($class->readDependecies() as $dependecy) {
                $args[] = $container->get($dependecy);
            }
            return $class->newInstanceArgs($args);
        } catch (ReflectionException $e) {
            throw new NotFound($name);
        }
    }

    public function hasProvide(string $name): bool
    {
        return class_exists($name);
    }
}
