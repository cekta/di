<?php
declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\InfiniteRecursion;
use Cekta\DI\Exception\NotFound;
use Cekta\DI\Exception\NotFoundInProvider;
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

    public function get($name)
    {
        $this->checkInfiniteRecursion($name);
        $this->calls[] = $name;
        if (!array_key_exists($name, $this->values)) {
            $this->values[$name] = $this->provide($this->getProvider($name), $name);
        }
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

    private function getProvider(string $name): ProviderInterface
    {
        $provider = $this->findProvider($name);
        if (null === $provider) {
            throw new NotFound($name);
        }
        return $provider;
    }

    /**
     * @param ProviderInterface $provider
     * @param string $name
     * @return mixed
     * @throws NotFoundInProvider
     */
    private function provide(ProviderInterface $provider, string $name)
    {
        try {
            return $provider->provide($name, $this);
        } catch (ProviderNotFoundException $e) {
            throw new NotFoundInProvider($name, $e);
        }
    }
}
