<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider\Exception;

use Cekta\DI\Provider\Exception\InvalidCacheKey;
use Cekta\DI\ProviderExceptionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

class InvalidCacheKeyTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $prev;
    /**
     * @var InvalidCacheKey
     */
    private $exception;
    /**
     * @var string
     */
    private $id;

    protected function setUp(): void
    {
        $this->prev = $this->createMock(Throwable::class);
        $this->id = 'magic';
        assert($this->prev instanceof Throwable);
        $this->exception = new InvalidCacheKey($this->id, $this->prev);
    }

    public function testMustProviderExceptionInterface()
    {
        $this->assertInstanceOf(ProviderExceptionInterface::class, $this->exception);
    }

    public function testCodeZero()
    {
        $this->assertSame(0, $this->exception->getCode());
    }

    public function testMessage()
    {
        $this->assertSame("Invalide cache key `{$this->id}`", $this->exception->getMessage());
    }
}
