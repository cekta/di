<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Autowiring\ReflectionClass;
use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class AutowiringSimpleCache implements ProviderInterface
{
    /** @var CacheInterface */
    private $cache;

    /** @var Autowiring */
    private $autowiring;

    public function __construct(CacheInterface $cache, ?Autowiring $autowiring = null)
    {
        $this->autowiring = $autowiring ?? new Autowiring();
        $this->cache = $cache;
    }

    public function provide(string $id, ContainerInterface $container)
    {
        $args = [];
        foreach ($this->getDependencies($id) as $dependency) {
            $args[] = $container->get($dependency);
        }
        return $this->autowiring->create($id, $args);
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }

    private function getDependencies(string $id): array
    {
        try {
            if (!$this->cache->has($id)) {
                $this->cache->set($id, $this->autowiring->getDependencies($id));
            }
            return $this->cache->get($id);
        } catch (InvalidArgumentException $e) {
            throw new InvalidCacheKey($id, $e);
        }
    }
}
