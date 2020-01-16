<?php

declare(strict_types=1);

namespace Cekta\DI\Provider\Autowiring;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * @internal
 */
class Reflection
{
    private static $dependencies = [];
    private static $instantiable = [];

    public function getDependencies(string $name)
    {
        if (!array_key_exists($name, self::$dependencies)) {
            $this->load($name);
        }
        return self::$dependencies[$name];
    }

    public function isInstantiable(string $name): bool
    {
        if (!array_key_exists($name, self::$instantiable)) {
            $this->load($name);
        }
        return self::$instantiable[$name];
    }

    private function load(string $name): void
    {
        try {
            $class = new ReflectionClass($name);
            self::$instantiable[$name] = $class->isInstantiable();
            $constructor = $class->getConstructor();
            self::$dependencies[$name] = $constructor ? self::getMethodParameters($constructor) : [];
        } catch (ReflectionException $exception) {
            self::$dependencies[$name] = [];
            self::$instantiable[$name] = false;
        }
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
