<?php
declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\NotFound;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    /**
     * @var ProviderInterface[]
     */
    private $providers;

    public function __construct(ProviderInterface ... $providers)
    {
        $this->providers = $providers;
    }

    public function get($name)
    {
        $provider = $this->findProvider($name);
        if (null === $provider) {
            throw new NotFound($name);
        }
        return $provider->provide($name, $this);
    }


    public function has($name)
    {
        return !is_null($this->findProvider($name));
    }

    private function findProvider(string $name): ?ProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->hasProvide($name)) {
                return $provider;
            }
        }
        return null;
    }
}
