<?php

declare(strict_types=1);

namespace Cekta\DI;

use ReflectionClass;
use ReflectionException;

class Reflection
{
    private Reflection\MethodService $reflectionMethod;

    public function __construct()
    {
        $this->reflectionMethod = new Reflection\MethodService();
    }

    /**
     * @param string $name
     * @return string[]
     * @throws ReflectionException
     */
    public function getDependencies(string $name): array
    {
        $class = new ReflectionClass($name);
        return $this->reflectionMethod->findDependencies($class->getConstructor());
    }

    /**
     * @param string $name
     * @return bool
     */
    public function isInstantiable(string $name): bool
    {
        try {
            $class = new ReflectionClass($name);
            return $class->isInstantiable();
        } catch (ReflectionException $exception) {
            return false;
        }
    }
}
