<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowire\Reader\Exception;

use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidCacheKey;
use PHPUnit\Framework\TestCase;
use Throwable;

class InvalidCacheKeyTest extends TestCase
{
    public function testMessage()
    {
        $prev = $this->createMock(Throwable::class);
        assert($prev instanceof Throwable);
        $exception = new InvalidCacheKey('className', 'key', $prev);
        $this->assertSame('Invalid key: `key` for class: `className`', $exception->getMessage());
        $this->assertSame($prev, $exception->getPrevious());
        $this->assertSame(0, $exception->getCode());
    }
}
