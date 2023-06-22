<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Test\Fixture\Builder;

class ContainerCompiledTest extends ContainerTest
{
    protected function setUp(): void
    {
        if (!isset($this->container)) {
            $builder = new Builder();
            $compiled = $builder->compile();
            file_put_contents(__DIR__ . '/ExampleCompiled.php', $compiled);
            $container = $builder->build();
            /** @psalm-var  class-string<object> $expected */
            $expected = 'Cekta\\DI\\Test\\ExampleCompiled';
            $this->assertInstanceOf($expected, $container);
            $this->container = $container;
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (file_exists(__DIR__ . '/ExampleCompiled.php')) {
            unlink(__DIR__ . '/ExampleCompiled.php');
        }
    }
}
