<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Exception\NotFound;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Autowire implements ProviderInterface
{
    public function provide(string $name, ContainerInterface $container)
    {
        try {
            $class = new ReflectionClass($name);
            $args = [];
            foreach ($this->getDependecies($class) as $dependecy) {
                $args[] = $container->get($dependecy);
            }
            return $class->newInstanceArgs($args);
        } catch (ReflectionException $e) {
            throw new NotFound($name);
        }
    }

    private function getDependecies(ReflectionClass $class): array
    {
        $contructor = $class->getConstructor();
        if (null === $contructor) {
            return [];
        }
        return $this->readConstructor($contructor);
    }

    private function readConstructor(ReflectionMethod $constructor): array
    {
        $result = [];
        foreach ($constructor->getParameters() as $parameter) {
            $class = $parameter->getClass();
            if (null !== $class) {
                $result[] = $class->name;
            } else {
                $result[] = $parameter->name;
            }
        }
        return $result;
    }

    public function hasProvide(string $name): bool
    {
        return class_exists($name);
    }
}
