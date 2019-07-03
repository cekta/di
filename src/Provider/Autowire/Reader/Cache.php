<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire\Reader;

use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidCacheKey;
use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidClassName;
use Cekta\DI\Provider\Autowire\ReaderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class Cache implements ReaderInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    private $pool;
    /**
     * @var WithoutCache
     */
    private $reader;

    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
        $this->reader = new WithoutCache();
    }

    /**
     * @param string $className
     * @return array
     * @throws InvalidClassName
     */
    public function getDependencies(string $className): array
    {
        $key = base64_encode($className);
        try {
            $item = $this->pool->getItem($key);
            if (!$item->isHit()) {
                $item->set($this->reader->getDependencies($className));
                $this->pool->save($item);
            }
            return $item->get();
        } catch (InvalidArgumentException $e) {
            throw new InvalidCacheKey($className, $key, $e);
        }
    }
}
