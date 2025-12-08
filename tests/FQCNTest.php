<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\FQCN;
use PHPUnit\Framework\TestCase;
use stdClass;

class FQCNTest extends TestCase
{
    public function testWithoutNamespace(): void
    {
        $obj = new FQCN('stdClass');
        $this->assertSame(stdClass::class, $obj->className);
        $this->assertSame('', $obj->namespace);
    }
}
