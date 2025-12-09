<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Dependency;
use PHPUnit\Framework\TestCase;

class DependencyTest extends TestCase
{
    public function testCreate(): void
    {
        $name = 'test';
        $obj = new Dependency($name);
        $this->assertSame($name, $obj->getName());
        $this->assertFalse($obj->isVariadic());

        $obj = new Dependency($name, true);
        $this->assertTrue($obj->isVariadic());
    }
}
