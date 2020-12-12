<?php

declare(strict_types=1);

namespace Cekta\DI;

use ReflectionClass;
use ReflectionException;

class Reflection
{
    /**
     * @var Reflection\MethodService
     */
    private $reflectionMethod;

    public function __construct()
    {
        $this->reflectionMethod = new Reflection\MethodService();
    }

    /**
     * @param string $name
     * @return string[]
     * @internal
     */
    public function getDependencies(string $name): array
    {
        try {
            $class = new ReflectionClass($name);
            return $this->reflectionMethod->findDependencies($class->getConstructor());
        } catch (ReflectionException $exception) {
            return [];
        }
    }

    /**
     * @param string $name
     * @return bool
     * @internal
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
