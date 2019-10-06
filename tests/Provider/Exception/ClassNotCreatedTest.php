<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider\Exception;

use Cekta\DI\Provider\Exception\ClassNotCreated;
use Cekta\DI\ProviderExceptionInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

class ClassNotCreatedTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $prev;
    /**
     * @var ClassNotCreated
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
        $this->exception = new ClassNotCreated($this->id, $this->prev);
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
        $this->assertSame("ReflectionClass not createable for `{$this->id}`", $this->exception->getMessage());
    }
}
