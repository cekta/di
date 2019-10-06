<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Loader\Service;
use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class AutowiringCache implements ProviderInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $pool;
    /**
     * @var Autowiring
     */
    private $autowiring;

    public function __construct(CacheItemPoolInterface $pool, Autowiring $autowiring)
    {
        $this->autowiring = $autowiring;
        $this->pool = $pool;
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
     * @return string
     */
    public static function getCacheKey(string $id): string
    {
        return strlen($id) > 64 ? hash('sha256', $id)
            : (string) preg_replace('/[^\w_.]/', '_', $id);
    }

    private function getDependencies(string $id): array
    {
        try {
            $item = $this->pool->getItem(self::getCacheKey($id));
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
