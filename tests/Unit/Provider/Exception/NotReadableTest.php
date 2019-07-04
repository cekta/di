<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Exception;

use Cekta\DI\Provider\Exception\NotReadable;
use PHPUnit\Framework\TestCase;
use Throwable;

class NotReadableTest extends TestCase
{
    public function testConstructor()
    {
        $prev = $this->createMock(Throwable::class);
        assert($prev instanceof Throwable);
        $e = new NotReadable('id', $prev);
        $this->assertSame('Container `id` not readable', $e->getMessage());
        $this->assertSame($prev, $e->getPrevious());
        $this->assertSame(0, $e->getCode());
    }
}
