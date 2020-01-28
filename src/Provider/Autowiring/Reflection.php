<?php

declare(strict_types=1);

namespace Cekta\DI\Provider\Autowiring;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Reflection
{
    private static $dependencies = [];
    private static $instantiable = [];

    /**
     * @param string $name
     * @return array<string>
     * @internal
     */
    public function getDependencies(string $name): array
    {
        if (!array_key_exists($name, self::$dependencies)) {
            $this->load($name);
        }
        return self::$dependencies[$name];
    }

    /**
     * @param string $name
     * @return bool
     * @internal
     */
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
            self::$dependencies[$name] = self::getMethodParameters($class->getConstructor());
        } catch (ReflectionException $exception) {
            self::$dependencies[$name] = [];
            self::$instantiable[$name] = false;
        }
    }

    private static function getMethodParameters(?ReflectionMethod $method): array
    {
        $result = [];
        if ($method !== null) {
            foreach ($method->getParameters() as $parameter) {
                $class = $parameter->getClass();
                $result[] = $class ? $class->name : $parameter->name;
            }
        }
        return $result;
    }
}
