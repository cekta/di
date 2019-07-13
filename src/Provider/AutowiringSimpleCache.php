<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Autowiring\ReflectionClass;
use Cekta\DI\Provider\Autowiring\RuleInterface;
use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

class AutowiringSimpleCache implements ProviderInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var RuleInterface[]
     */
    private $rules;

    public function __construct(CacheInterface $cache, RuleInterface ...$rules)
    {
        $this->cache = $cache;
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
            if (!$this->cache->has($id)) {
                $class = new ReflectionClass($id, ...$this->rules);
                $this->cache->set($id, $class->getDependencies());
            }
            return $this->cache->get($id);
        } catch (InvalidArgumentException $e) {
            throw new InvalidCacheKey($id, $e);
        } catch (ReflectionException $e) {
            throw new ClassNotCreated($id, $e);
        }
    }

}
