<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Autowiring\ReflectionClass;
use Cekta\DI\Provider\Autowiring\RuleInterface;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionException;

class AutowiringCache implements ProviderInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $pool;
    /**
     * @var RuleInterface[]
     */
    private $rules;

    public function __construct(CacheItemPoolInterface $pool, RuleInterface ...$rules)
    {
        $this->pool = $pool;
        $this->rules = $rules;
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
            $item = $this->pool->getItem($id);
            if (!$item->isHit()) {
                $class = new ReflectionClass($id, ...$this->rules);
                $item->set($class->getDependencies());
                $this->pool->save($item);
            }
            return $item->get();
        } catch (InvalidArgumentException $e) {
            throw new InvalidCacheKey($id, $e);
        } catch (ReflectionException $e) {
            throw new ClassNotCreated($id, $e);
        }
    }
}
