<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\ProviderInterface;
use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Autowiring implements ProviderInterface
{
    public function provide(string $id)
    {
        return self::createObject($id, $this->getDependencies($id));
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }

    public static function getDependencies(string $id): array
    {
        try {
            $constructor = (new ReflectionClass($id))->getConstructor();
            return $constructor ? self::getMethodParameters($constructor) : [];
        } catch (ReflectionException $reflectionException) {
            throw new ClassNotCreated($id, $reflectionException);
        }
    }

    public static function createObject(string $name, array $dependencies): Closure
    {
        return static function (ContainerInterface $container) use ($dependencies, $name) {
            $args = [];
            foreach ($dependencies as $dependecy) {
                $args[] = $container->get($dependecy);
            }
            return new $name(...$args);
        };
    }

    private static function getMethodParameters(ReflectionMethod $method): array
    {
        $result = [];
        foreach ($method->getParameters() as $parameter) {
            $class = $parameter->getClass();
            $result[] = $class ? $class->name : $parameter->name;
        }
        return $result;
    }
}
