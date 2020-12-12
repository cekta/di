<?php

declare(strict_types=1);

namespace Cekta\DI\Reflection;

use ReflectionMethod;

class MethodService
{
    /**
     * @var ParameterService
     */
    private $parameter;

    public function __construct()
    {
        $this->parameter = new ParameterService();
    }
    /**
     * @param ReflectionMethod|null $method
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
     * @param string $comment
     * @return array<string>
     */
    private function getAnnotationParameters(string $comment): array
    {
        $result = [];
        $matches = [];
        preg_match_all("/@inject \\\\?([\w\d\\\\]*) \\$([\w\d]*)/", $comment, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $result[$match[2]] = $match[1];
        }
        return $result;
    }

    /**
     * @param ReflectionMethod $method
     * @return array<string>
     */
    private function getDependencies(ReflectionMethod $method): array
    {
        $parameters = [];
        $annotations = $this->getAnnotationParameters((string) $method->getDocComment());
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = $this->parameter->getName($parameter, $annotations);
        }
        return $parameters;
    }
}
