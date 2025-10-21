<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\DependencyDTO;
use PHPUnit\Framework\TestCase;

class DependencyDTOTest extends TestCase
{
    public function testCreate(): void
    {
        $name = 'test';
        $obj = new DependencyDTO($name);
        $this->assertSame($name, $obj->getName());
        $this->assertFalse($obj->isVariadic());

        $obj = new DependencyDTO($name, true);
        $this->assertTrue($obj->isVariadic());
    }
}
