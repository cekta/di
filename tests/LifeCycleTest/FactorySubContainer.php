<?php

declare(strict_types=1);

namespace Cekta\DI\Test\LifeCycleTest;

use Cekta\DI\Test\LifeCycleTest\FactorySubContainer\Dependency;

class FactorySubContainer
{
    public function __construct(public Dependency $dependency)
    {
    }
}
