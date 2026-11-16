<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\DependencyMap;
use Cekta\DI\DependencyMap\Dependency\AutowiringShared;
use Cekta\DI\FQCN;
use Cekta\DI\Manifest;
use Cekta\DI\Test\DependencyMapTest\Entrypoint1;
use Cekta\DI\Test\DependencyMapTest\Entrypoint2;
use Cekta\DI\Test\DependencyMapTest\SomeSharedDependency;
use PHPUnit\Framework\TestCase;

class DependencyMapTest extends TestCase
{
    public function testAutowiringToShared(): void
    {
        $map = new DependencyMap();
        $manifest = new Manifest(
            filename: '/tmp/Container.php',
            fqcn: 'App\Container',
            definition: [
                'entries' => [
                    Entrypoint1::class,
                    Entrypoint2::class,
                ],
            ],
        );
        $result = $map->generate($manifest);
        $this->assertInstanceOf(AutowiringShared::class, $result[SomeSharedDependency::class]);
    }
}
