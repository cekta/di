<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\Exception\NotFound;
use Cekta\DI\Provider\KeyValue;
use Cekta\DI\ProviderExceptionInterface;
use Cekta\DI\ProviderInterface;
use PHPUnit\Framework\TestCase;

class KeyValueTest extends TestCase
{
    public function testMustBeProvider(): void
    {
        $this->assertInstanceOf(ProviderInterface::class, new KeyValue([]));
    }

    public function testCanProvide(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        static::assertTrue($provider->canProvide('key'));
        static::assertFalse($provider->canProvide('invalid name'));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvide(): void
    {
        $provider = new KeyValue(['key' => 'value']);
        static::assertEquals('value', $provider->provide('key'));
    }

    /**
     * @throws ProviderExceptionInterface
     */
    public function testProvideNotFound(): void
    {
        $this->expectException(NotFound::class);
        (new KeyValue([]))->provide('magic');
    }
}
