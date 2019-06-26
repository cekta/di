<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Exception;

use Cekta\DI\Exception\NotFoundInProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

class NotFoundInProviderTest extends TestCase
{
    public function testMessage()
    {
        $prev = $this->createMock(Throwable::class);
        assert($prev instanceof Throwable);
        $e = new NotFoundInProvider('123', $prev);
        static::assertSame('Provider cant load container `123`', $e->getMessage());
        static::assertSame(0, $e->getCode());
    }
}
