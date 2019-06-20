<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Exception\NotFound;
use Cekta\DI\Provider\Autowire\Reflection;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;

class Autowire implements ProviderInterface
{
    public function provide(string $name, ContainerInterface $container)
    {
        try {
            $class = new ReflectionClass($name);
            $args = [];
            foreach (Reflection::getDependecies($class) as $dependecy) {
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
