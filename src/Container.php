<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\ProviderNotFound;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var Provider[]
     */
    private $providers;
    /**
     * @var array<string, mixed>
     */
    private $values = [];
    /**
     * @var string[]
     */
    private $calls = [];

    public function __construct(Provider ...$providers)
    {
        $this->providers = $providers;
    }

    public function get($id)
    {
        if (!array_key_exists($id, $this->values)) {
            $this->checkInfiniteRecursion($id);
            $provider = $this->findProvider($id);
            if ($provider === null) {
                throw new ProviderNotFound($id);
            }
            $this->values[$id] = $this->load($provider->provide($id));
        }
        return $this->values[$id];
    }

    public function has($name)
    {
        return array_key_exists($name, $this->values) || $this->findProvider($name) !== null;
    }

    private function findProvider(string $name): ?Provider
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
        $this->calls[] = $id;
    }

    /**
     * @param mixed|callable$result
     * @return mixed
     */
    private function load($result)
    {
        if (is_callable($result)) {
            $result = call_user_func($result, $this);
        }
        return $result;
    }
}
