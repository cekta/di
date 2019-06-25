<?php
declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\NotFound;
use Cekta\DI\Exception\NotFoundInProvider;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private $providers;
    private $values = [];
    private $calls = [];

    public function __construct(ProviderInterface ... $providers)
    {
        $this->providers = $providers;
    }

    public function get($name)
    {
        $this->checkInfiniteRecursion($name);
        $this->calls[] = $name;
        $this->tryLoad($name);
        array_pop($this->calls);
        return $this->values[$name];
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

    /**
     * @param string $name
     * @throws InfiniteRecursion
     */
    private function checkInfiniteRecursion(string $name): void
    {
        if (in_array($name, $this->calls)) {
            throw new InfiniteRecursion($name, $this->calls);
        }
    }

    /**
     * @param string $name
     * @throws NotFoundInProvider
     */
    private function tryLoad(string $name): void
    {
        if (!array_key_exists($name, $this->values)) {
            $provider = $this->getProvider($name);
            try {
                $this->values[$name] = $provider->provide($name, $this);
            } catch (ProviderNotFoundException $e) {
                throw new NotFoundInProvider($name, $e);
            }
        }
    }

    /**
     * @param string $name
     * @return ProviderInterface
     */
    private function getProvider(string $name)
    {
        $provider = $this->findProvider($name);
        if (null === $provider) {
            throw new NotFound($name);
        }
        return $provider;
    }
}
