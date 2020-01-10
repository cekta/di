<?php

declare(strict_types=1);

namespace Cekta\DI\Provider\Autowiring;

use ReflectionException;
use ReflectionMethod;

/**
 * @internal
 */
class Reflection
{
    private static $classes = [];

    public function getClass(string $name): ReflectionClass
    {
        if (!array_key_exists($name, self::$classes)) {
            try {
                $class = new \ReflectionClass($name);
                $instantiable = $class->isInstantiable();
                $constructor = $class->getConstructor();
                $dependencies = $constructor ? self::getMethodParameters($constructor) : [];
                self::$classes[$name] = new ReflectionClass($instantiable, ...$dependencies);
            } catch (ReflectionException $exception) {
                self::$classes[$name] = new ReflectionClass(false);
            }
        }
        return self::$classes[$name];
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
