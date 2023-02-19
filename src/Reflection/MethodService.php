<?php

declare(strict_types=1);

namespace Cekta\DI\Reflection;

use ReflectionMethod;

/**
 * @internal
 */
class MethodService
{
    private ParameterService $parameter;

    public function __construct()
    {
        $this->parameter = new ParameterService();
    }

    /**
     * @param ?ReflectionMethod $method
     * @return array<string>
     */
    public function findDependencies(?ReflectionMethod $method): array
    {
        $parameters = [];
        if ($method !== null) {
            $parameters = $this->getDependencies($method);
        }
        return $parameters;
    }

    /**
     * @param ReflectionMethod $method
     * @return array<string>
     */
    private function getDependencies(ReflectionMethod $method): array
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = $this->parameter->getName($parameter);
        }
        return $parameters;
    }
}
