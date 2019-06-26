<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowire\Exception;

use Cekta\DI\Provider\Autowire\Exception\NotFound;
use PHPUnit\Framework\TestCase;
use Throwable;

class NotFoundTest extends TestCase
{
    public function testMessage()
    {
        $prev = $this->createMock(Throwable::class);
        assert($prev instanceof Throwable);
        $e = new NotFound('123', $prev);
        static::assertSame('Container `123` not found', $e->getMessage());
        static::assertSame(0, $e->getCode());
    }
}
