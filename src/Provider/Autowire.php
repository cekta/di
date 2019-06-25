<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Exception\NotFound;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;

class Autowire implements ProviderInterface
{
    public function provide(string $id, ContainerInterface $container)
    {
        try {
            $class = new Autowire\ReflectionClass($id);
            $args = [];
            foreach ($class->getDependencies() as $dependecy) {
                $args[] = $container->get($dependecy);
            }
            return $class->newInstanceArgs($args);
        } catch (ReflectionException $e) {
            throw new NotFound($id);
        }
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }
}
