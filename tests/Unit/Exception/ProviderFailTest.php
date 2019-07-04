<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Exception;

use Cekta\DI\Exception\NotProvideble;
use PHPUnit\Framework\TestCase;
use Throwable;

class ProviderFailTest extends TestCase
{
    public function testMessage()
    {
        $prev = $this->createMock(Throwable::class);
        assert($prev instanceof Throwable);
        $e = new NotProvideble('123', $prev);
        static::assertSame('Provider cant load container `123`', $e->getMessage());
        static::assertSame(0, $e->getCode());
    }
}
