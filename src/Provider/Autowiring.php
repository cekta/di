<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Loader\Service;
use Cekta\DI\Provider\Autowiring\RuleInterface;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\ProviderInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class Autowiring implements ProviderInterface
{
    /**
     * @var RuleInterface[]
     */
    private $rules;

    public function __construct(RuleInterface ...$rules)
    {
        $this->rules = $rules;
    }

    public function provide(string $id)
    {
        return Service::createObject($id, $this->getDependencies($id));
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }

    /**
     * @internal
     * @param string $id
     * @return string[]
     */
    public function getDependencies(string $id): array
    {
        try {
            $constructor = (new ReflectionClass($id))->getConstructor();
            return $constructor ? $this->getMethodParameters($id, $constructor) : [];
        } catch (ReflectionException $e) {
            throw new ClassNotCreated($id, $e);
        }
    }

    private function getMethodParameters(string $id, ReflectionMethod $method): array
    {
        $replaces = $this->getReplaces($id);
        $result = [];

        foreach ($method->getParameters() as $parameter) {
            $class = $parameter->getClass();
            $name = $class ? $class->name : $parameter->name;
            $result[] = array_key_exists($name, $replaces) ? $replaces[$name] : $name;
        }

        return $result;
    }

    private function getReplaces(string $id): array
    {
        $accept = static function (array $result, RuleInterface $rule) use ($id) {
            if ($rule->acceptable($id)) {
                $result = array_merge($result, $rule->accept());
            }
            return $result;
        };

        return array_reduce($this->rules, $accept, []);
    }
}
