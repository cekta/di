<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Autowire\Exception\NotFound;
use Cekta\DI\Provider\Autowire\RuleInterface;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;

class Autowire implements ProviderInterface
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
        try {
            $class = new Autowire\ReflectionClass($id, ...$this->rules);
            /** @var mixed[] $args */
            $args = [];
            foreach ($class->getDependencies() as $dependecy) {
                $args[] = $container->get($dependecy);
            }
            return $class->newInstanceArgs($args);
        } catch (ReflectionException $e) {
            throw new NotFound($id, $e);
        }
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }
}
