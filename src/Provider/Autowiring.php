<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Autowiring\Reader;
use Cekta\DI\Provider\Autowiring\ReflectionClass;
use Cekta\DI\Provider\Autowiring\RuleInterface;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;

class Autowiring implements ProviderInterface
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(?Reader $reader)
    {
        if (null === $reader) {
            $reader = new Reader();
        }
        $this->reader = $reader;
    }

    public function provide(string $id, ContainerInterface $container)
    {
        $args = [];
        foreach ($this->getDependencies($id) as $dependecy) {
            $args[] = $container->get($dependecy);
        }
        return new $id(...$args);
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }

    private function getDependencies(string $id): array
    {
        try {
            return $this->reader->getDependencies($id);
        } catch (ReflectionException $e) {
            throw new ClassNotCreated($id, $e);
        }
    }
}
