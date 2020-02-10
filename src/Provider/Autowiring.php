<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Loader\Factory;
use Cekta\DI\Loader\FactoryVariadic;
use Cekta\DI\ProviderInterface;
use Cekta\DI\Reflection;

class Autowiring implements ProviderInterface
{
    /**
     * @var Reflection
     */
    private $reflection;

    public function __construct(Reflection $reflection)
    {
        $this->reflection = $reflection;
    }

    public function provide(string $id)
    {
        if ($this->reflection->isVariadic($id)) {
            return new FactoryVariadic($id, ...$this->reflection->getDependencies($id));
        }
        return new Factory($id, ...$this->reflection->getDependencies($id));
    }

    public function canProvide(string $id): bool
    {
        return $this->reflection->isInstantiable($id);
    }
}
