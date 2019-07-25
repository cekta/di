<?php
declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\ProviderNotFound;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use TypeError;

class Container implements ContainerInterface
{

    /** @var ProviderInterface[] */
    private $providers;

    /** @var array */
    private $values = [];

    /** @var string[] */
    private $calls = [];

    public function __construct(ProviderInterface ... $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @param  string|mixed  $id
     *
     * @return mixed
     */
    public function get($id)
    {
        if (!is_string($id)) {
            throw new TypeError('id must be a string');
        }

        if ($this->isInfiniteRecursion($id)) {
            throw new InfiniteRecursion($id, $this->calls);
        }

        $this->calls[] = $id;
        if (!array_key_exists($id, $this->values)) {
            $provider = $this->getProvider($id);
            $this->values[$id] = $provider->provide($id, $this);
        }
        array_pop($this->calls);
        return $this->values[$id];
    }

    public function has($name): bool
    {
        return $this->findProvider($name) !== null;
    }

    private function findProvider(string $name): ?ProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->canBeProvided($name)) {
                return $provider;
            }
        }
        return null;
    }

    private function isInfiniteRecursion(string $id): bool
    {
        return in_array($id, $this->calls);
    }

    private function getProvider(string $id): ProviderInterface
    {
        if ($provider = $this->findProvider($id)) {
            return $provider;
        }

        throw new ProviderNotFound($id);
    }
}
