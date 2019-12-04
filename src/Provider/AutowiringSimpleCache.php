<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class AutowiringSimpleCache implements ProviderInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var Autowiring
     */
    private $autowiring;

    public function __construct(CacheInterface $cache, Autowiring $autowiring)
    {
        $this->autowiring = $autowiring;
        $this->cache = $cache;
    }

    public function provide(string $id)
    {
        return Autowiring::createObject($id, $this->getDependencies($id));
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }

    private function getDependencies(string $id): array
    {
        try {
            $key = AutowiringCache::getCacheKey($id);
            $result = $this->cache->get($key);
            if ($result === null) {
                $result = $this->autowiring->getDependencies($id);
                $this->cache->set($key, $result);
            }
            return $result;
        } catch (InvalidArgumentException $exception) {
            throw new InvalidCacheKey($id, $exception);
        }
    }
}
