<?php
declare(strict_types=1);

namespace Cekta\DI\Provider\Autowire\Reader;

use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidCacheKey;
use Cekta\DI\Provider\Autowire\ReaderInterface;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class SimpleCache implements ReaderInterface
{
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var ReaderInterface
     */
    private $reader;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->reader = new WithoutCache();
    }

    /**
     * @param string $className
     * @return array
     */
    public function getDependencies(string $className): array
    {
        $key = base64_encode($className);
        try {
            if (!$this->cache->has($key)) {
                $this->cache->set($key, $this->reader->getDependencies($className));
            }
            return $this->cache->get($key);
        } catch (InvalidArgumentException $e) {
            throw new InvalidCacheKey($className, $key, $e);
        }
    }
}
