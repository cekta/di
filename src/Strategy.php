<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\NotFound;
use Psr\Container\ContainerInterface;

class Strategy implements ContainerInterface
{
    /**
     * @var ContainerInterface[]
     */
    private array $containers;

    public function __construct(ContainerInterface ...$containers)
    {
        $this->containers = $containers;
    }

    public function get($id)
    {
        $provider = $this->findProvider($id);
        if ($provider === null) {
            throw new NotFound($id);
        }
        return $provider->get($id);
    }

    public function has(string $id): bool
    {
        return $this->findProvider($id) !== null;
    }

    private function findProvider(string $id): ?ContainerInterface
    {
        foreach ($this->containers as $provider) {
            if ($provider->has($id)) {
                return $provider;
            }
        }
        return null;
    }
}
