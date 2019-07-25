<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Autowiring\RuleInterface;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use stdClass;

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

    public function provide(string $id, ContainerInterface $container)
    {
        $args = [];
        foreach ($this->getDependencies($id) as $dependecy) {
            $args[] = $container->get($dependecy);
        }
        return $this->create($id, $args);
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }

    public function create(string $id, array $args): object
    {
        return new $id(...$args);
    }

    /**
     * @internal
     * @param string $id
     * @return string[]
     */
    public function getDependencies(string $id): array
    {
        try {
            $class = new ReflectionClass($id);
            $constructor = $class->getConstructor();
            if (null === $constructor) {
                return [];
            }
            return $this->getMethodParameters($id, $constructor);
        } catch (ReflectionException $e) {
            throw new ClassNotCreated($id, $e);
        }
    }

    private function getMethodParameters(string $id, ReflectionMethod $method): array
    {
        $result = [];
        $replaces = $this->getReplaces($id);
        foreach ($method->getParameters() as $parameter) {
            $name = $this->getParameterName($parameter);
            if (array_key_exists($name, $replaces)) {
                $name = $replaces[$name];
            }
            $result[] = $name;
        }
        return $result;
    }
    private function getParameterName(ReflectionParameter $parameter): string
    {
        $class = $parameter->getClass();
        if (null !== $class) {
            return $class->name;
        }
        return $parameter->name;
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
