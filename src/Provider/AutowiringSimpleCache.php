<?php
declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Loader\Service;
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
        return Service::createObject($id, $this->getDependencies($id));
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }

    private function getDependencies(string $id): array
    {
        try {
            $key = AutowiringCache::getCacheKey($id);
            if (!$this->cache->has($key)) {
                $this->cache->set($key, $this->autowiring->getDependencies($id));
            }
            return $this->cache->get($id);
        } catch (InvalidArgumentException $e) {
            throw new InvalidCacheKey($id, $e);
        }
    }
}
