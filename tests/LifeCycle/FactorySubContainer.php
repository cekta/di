<?php

declare(strict_types=1);

namespace Cekta\DI\Test\LifeCycle;

use Cekta\DI\Test\LifeCycle\FactorySubContainer\Dependency;

class FactorySubContainer
{
    public function __construct(public Dependency $dependency)
    {
    }
}
