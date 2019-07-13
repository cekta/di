<?php
declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\IdNotString;
use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\ProviderNotFound;
use Psr\Container\ContainerExceptionInterface;
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
     * @var string[]
     */
    private $calls = [];

    public function __construct(ProviderInterface ... $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @param mixed $id
     * @return mixed
     * @throws ContainerExceptionInterface
     */
    public function get($id)
    {
        if (!is_string($id)) {
            throw new IdNotString();
        }
        $this->checkInfiniteRecursion($id);
        $this->calls[] = $id;
        if (!array_key_exists($id, $this->values)) {
            $provider = $this->getProvider($id);
            $this->values[$id] = $provider->provide($id, $this);
        }
        array_pop($this->calls);
        return $this->values[$id];
    }

    public function has($name)
    {
        return !is_null($this->findProvider($name));
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
        if (in_array($id, $this->calls)) {
            throw new InfiniteRecursion($id, $this->calls);
        }
    }

    private function getProvider(string $id): ProviderInterface
    {
        $provider = $this->findProvider($id);
        if (null === $provider) {
            throw new ProviderNotFound($id);
        }
        return $provider;
    }
}
