<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Exception;

use Cekta\DI\Exception\IdNotString;
use PHPUnit\Framework\TestCase;

class IdNotStringTest extends TestCase
{
    public function testMessage()
    {
        $e = new IdNotString();
        $this->assertSame('Container ID must be string', $e->getMessage());
    }
}
