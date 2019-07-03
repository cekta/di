<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Exception;

use Cekta\DI\Exception\InfiniteRecursion;
use PHPUnit\Framework\TestCase;

class InfiniteRecursionTest extends TestCase
{
    public function testMessage(): void
    {
        $e = new InfiniteRecursion('test', ['test', 'foo']);
        $this->assertSame('Infinite recursion for `test`, calls: `test, foo`', $e->getMessage());
    }
}
