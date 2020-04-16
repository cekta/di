<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Loader\Factory;
use Cekta\DI\Loader\FactoryVariadic;
use Cekta\DI\Provider;
use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\Reflection;

class Autowiring implements Provider
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
        if (!$this->canProvide($id)) {
            throw new NotFound($id);
        }
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
