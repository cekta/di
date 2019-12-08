<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

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

    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    public function provide(string $id)
    {
        return Autowiring::createObject($id, $this->getDependencies($id));
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }

    /**
     * @param string $id
     * @return string
     * @internal
     */
    public static function getCacheKey(string $id): string
    {
        if (strlen($id) > 64) {
            return hash('sha256', $id);
        }
        $result = preg_replace('/[^\w\d_.]/', '_', $id);
        assert(is_string($result));
        return $result;
    }

    private function getDependencies(string $id): array
    {
        try {
            $item = $this->pool->getItem($this->getCacheKey($id));
            if (!$item->isHit()) {
                $item->set(Autowiring::getDependencies($id));
                $this->pool->save($item);
            }
            return $item->get();
        } catch (InvalidArgumentException $exception) {
            throw new InvalidCacheKey($id, $exception);
        }
    }
}
