<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Container\ContainerInterface;

class AutowiringCache implements ProviderInterface
{
    /** @var CacheItemPoolInterface */
    private $pool;

    /** @var Autowiring */
    private $autowiring;

    public function __construct(CacheItemPoolInterface $pool, ?Autowiring $autowiring = null)
    {
        $this->autowiring = $autowiring ?? new Autowiring();
        $this->pool = $pool;
    }

    public function provide(string $id, ContainerInterface $container)
    {
        foreach ($this->getDependencies($id) as $dependency) {
            $args[] = $container->get($dependency);
        }

        return new $id(...$args ?? []);
    }

    public function canBeProvided(string $id): bool
    {
        return class_exists($id);
    }

    private function getDependencies(string $id): array
    {
        try {
            /** @var \Psr\Cache\CacheItemInterface $item */
            $item = $this->pool->getItem($id);
            if (!$item->isHit()) {
                $item->set($this->autowiring->getDependencies($id));
                $this->pool->save($item);
            }
            return $item->get();
        } catch (InvalidArgumentException $e) {
            throw new InvalidCacheKey($id, $e);
        }
    }
}
