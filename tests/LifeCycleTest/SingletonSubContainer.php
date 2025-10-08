<?php

declare(strict_types=1);

namespace Cekta\DI\Test\LifeCycleTest;

use Cekta\DI\Test\LifeCycleTest\SingletonSubContainer\Dependency;

class SingletonSubContainer
{
    public function __construct(public Dependency $dependency)
    {
    }
}
