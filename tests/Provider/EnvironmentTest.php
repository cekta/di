<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Provider\Environment;
use Cekta\DI\ProviderException;
use Cekta\DI\Provider;
use PHPUnit\Framework\TestCase;

class EnvironmentTest extends TestCase
{
    public function testMustBeProvider(): void
    {
        $this->assertInstanceOf(Provider::class, new Environment([]));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideAndCanProvide(): void
    {
        $provider = new Environment(
            [
                'a' => 123,
                'b' => 'true',
                'b2' => '(true)',
                'c' => 'false',
                'c2' => '(false)',
                'e' => 'empty',
                'e2' => '(empty)',
                'n' => 'null',
                'n2' => '(null)',
                'r' => '"test"',
                'r2' => "'test'",
                'REGISTER' => 'True'
            ]
        );
        $this->assertSame(123, $provider->provide('a'));
        $this->assertTrue($provider->provide('b'));
        $this->assertTrue($provider->provide('b2'));
        $this->assertTrue($provider->provide('REGISTER'));
        $this->assertFalse($provider->provide('c'));
        $this->assertFalse($provider->provide('c2'));
        $this->assertEmpty($provider->provide('e'));
        $this->assertEmpty($provider->provide('e2'));
        $this->assertNull($provider->provide('n'));
        $this->assertNull($provider->provide('n2'));
        $this->assertSame('test', $provider->provide('r'));
        $this->assertSame('test', $provider->provide('r2'));
    }
}
