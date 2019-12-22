<?php

declare(strict_types=1);

namespace Cekta\DI\Provider\Autowiring;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Reflection
{
    /**
     * @param string $name
     * @return array<string>
     * @throws ReflectionException
     * @internal
     */
    public function getDependencies(string $name): array
    {
        $constructor = (new ReflectionClass($name))->getConstructor();
        return $constructor ? $this->getMethodParameters($constructor) : [];
    }

    private function getMethodParameters(ReflectionMethod $method): array
    {
        $result = [];
        foreach ($method->getParameters() as $parameter) {
            $class = $parameter->getClass();
            $result[] = $class ? $class->name : $parameter->name;
        }
        return $result;
    }
}
