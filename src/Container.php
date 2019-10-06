<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\ProviderNotFound;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var ProviderInterface[]
     */
    private $providers;

    /**
     * @var array
     */
    private $values = [];

    /**
     * Record call stack to prevent infinite recursion
     * @var string[]
     */
    private $calls = [];

    public function __construct(ProviderInterface ...$providers)
    {
        $this->providers = $providers;
    }

    public function get($id)
    {
        if (!array_key_exists($id, $this->values)) {
            $this->values[$id] = $this->getValue($id);
        }
        return $this->values[$id];
    }

    public function has($name): bool
    {
        return $this->findProvider($name) !== null;
    }

    private function findProvider(string $name): ?ProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->canProvide($name)) {
                return $provider;
            }
        }
        return null;
    }

    private function checkInfiniteRecursion(string $id): void
    {
        if (in_array($id, $this->calls, true)) {
            throw new InfiniteRecursion($id, $this->calls);
        }
        $this->calls[] = $id;
    }

    private function getProvider(string $id): ProviderInterface
    {
        $provider = $this->findProvider($id);
        if (null === $provider) {
            throw new ProviderNotFound($id);
        }
        return $provider;
    }

    /**
     * @param string $id
     * @return mixed
     * @throws ProviderExceptionInterface
     */
    private function getValue(string $id)
    {
        $this->checkInfiniteRecursion($id);
        $provider = $this->getProvider($id);
        $result = $provider->provide($id);
        return $result instanceof LoaderInterface ? $result($this) : $result;
    }
}
