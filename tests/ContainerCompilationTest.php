<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\ContainerBuilder;
use Cekta\DI\Test\Fixture\Example4;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use UnexpectedValueException;

class ContainerCompilationTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testFail(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('`invalid container` is cant be resolved');
        (new ContainerBuilder())
            ->compile(['invalid container']);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testWithoutNamespace(): void
    {
        $builder = new ContainerBuilder();
        $builder->fqcn('\\Container');
        $compiled = $builder->compile([Example4::class]);
        $this->assertIsString($compiled);
        $this->assertStringNotContainsString('namespace', $compiled);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testInvalidNamespace(): void
    {
        $builder = new ContainerBuilder();
        $fqcn = 'Container';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid fqcn: `{$fqcn}` must contain \\");
        $builder->fqcn($fqcn);
        $builder->compile([Example4::class]);
    }
}
