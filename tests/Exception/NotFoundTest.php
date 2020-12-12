<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Exception;

use Cekta\DI\Exception\NotFound;
use PHPUnit\Framework\TestCase;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundTest extends TestCase
{
    public function testMustImplementPsrNotFound(): void
    {
        $exception = new NotFound('test');
        $this->assertInstanceOf(NotFoundExceptionInterface::class, $exception);
    }
    public function testMessage(): void
    {
        $exception = new NotFound('id');
        $this->assertSame('Container `id` not found', $exception->getMessage());
    }
}
