<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Autowiring\RuleInterface;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\ProviderInterface;
use Closure;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Autowiring implements ProviderInterface
{
    /**
     * @var array<RuleInterface>
     */
    private $rules;

    public function __construct(RuleInterface ...$rules)
    {
        $this->rules = $rules;
    }

    public function provide(string $id)
    {
        return self::createObject($id, $this->getDependencies($id));
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }

    public function getDependencies(string $id): array
    {
        try {
            $constructor = (new ReflectionClass($id))->getConstructor();
            return $constructor ? $this->getMethodParameters($id, $constructor) : [];
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

    private function getMethodParameters(string $id, ReflectionMethod $method): array
    {
        $result = [];
        $replaces = $this->getReplaces($id);
        foreach ($method->getParameters() as $parameter) {
            $class = $parameter->getClass();
            $name = $class ? $class->name : $parameter->name;
            $result[] = array_key_exists($name, $replaces) ? $replaces[$name] : $name;
        }
        return $result;
    }

    private function getReplaces(string $id)
    {
        $result = [];
        foreach ($this->rules as $rule) {
            if ($rule->acceptable($id)) {
                $result += $rule->accept();
            }
        }
        return $result;
    }
}
