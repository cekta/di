<?php

declare(strict_types=1);

namespace Cekta\DI\Test\LifeCycle;

use Cekta\DI\Test\LifeCycle\SingletonSubContainer\Dependency;

class SingletonSubContainer
{
    public function __construct(public Dependency $dependency)
    {
    }
}
