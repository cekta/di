<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerBuilder;
use Cekta\DI\DependencyMap;
use Cekta\DI\Test\DependencyMapTest\Entrypoint1;
use Cekta\DI\Test\DependencyMapTest\Entrypoint2;
use Cekta\DI\Test\DependencyMapTest\SomeSharedDependency;
use PHPUnit\Framework\TestCase;

class DependencyMapTest extends TestCase
{
    public function testAutowiringToShared(): void
    {
        $map = new DependencyMap();
        $config = new ContainerBuilder([
            Entrypoint1::class,
            Entrypoint2::class,
        ]);
        $result = $map->generate($config);
        $this->assertInstanceOf(DependencyMap\Dependency\AutowiringShared::class, $result[SomeSharedDependency::class]);
    }
}
