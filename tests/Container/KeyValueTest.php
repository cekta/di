<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Container;

use Cekta\DI\Container\KeyValue;
use Cekta\DI\Exception\NotFound;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class KeyValueTest extends TestCase
{
    public function testMustBePsrContainer(): void
    {
        $this->assertInstanceOf(ContainerInterface::class, new KeyValue([]));
    }

    public function testCanProvide(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        static::assertTrue($provider->has('key'));
        static::assertFalse($provider->has('invalid name'));
    }

    public function testGet(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        static::assertEquals('value', $provider->get('key'));
    }

    public function testGetNotFound(): void
    {
        $this->expectException(NotFound::class);
        (new KeyValue([]))->get('magic');
    }
}
