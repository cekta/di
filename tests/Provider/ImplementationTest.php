<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Provider;

use Cekta\DI\Loader\Alias;
use Cekta\DI\Provider\Implementation;
use Cekta\DI\ProviderException;
use Cekta\DI\Provider;
use PHPUnit\Framework\TestCase;

class ImplementationTest extends TestCase
{
    public function testMustBeProvider(): void
    {
        $this->assertInstanceOf(Provider::class, new Implementation([]));
    }

    /**
     * @throws ProviderException
     */
    public function testProvideAndCanProvide(): void
    {
        $provider = new Implementation(
            [
                'a' => 'string',
                'b' => 123
            ]
        );
        $this->assertSame(123, $provider->provide('b'));
        $this->assertInstanceOf(Alias::class, $provider->provide('a'));
        $this->assertTrue($provider->canProvide('a'));
        $this->assertTrue($provider->canProvide('b'));
        $this->assertFalse($provider->canProvide('invalide name'));
    }
}
