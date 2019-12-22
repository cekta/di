<?php

declare(strict_types=1);

namespace Cekta\DI\Provider;

use Cekta\DI\Loader\Obj;
use Cekta\DI\Provider\Autowiring\Reflection;
use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\ProviderInterface;

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
        if (!$this->canProvide($id)) {
            throw new NotFound($id);
        }
        /** @noinspection PhpUnhandledExceptionInspection */
        return new Obj($id, $this->reflection->getDependencies($id));
    }

    public function canProvide(string $id): bool
    {
        return class_exists($id);
    }
}
