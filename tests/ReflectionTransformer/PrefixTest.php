<?php

declare(strict_types=1);

namespace Cekta\DI\Test\ReflectionTransformer;

use Cekta\DI\ReflectionTransformer;
use Cekta\DI\ReflectionTransformer\Prefix;
use PHPUnit\Framework\TestCase;

class PrefixTest extends TestCase
{
    public function testTransform()
    {
        $transformer = new Prefix('\prefix', ['a' => 'b']);
        $this->assertSame(['b', 'c'], $transformer->transform('\prefix\Some', ['a' , 'c']));
    }

    public function testTransformWithoutModification()
    {
        $transformer = new Prefix('\other', ['a' => 'b']);
        $this->assertSame(['a', 'c'], $transformer->transform('\prefix\Some', ['a' , 'c']));
    }

    public function testMustBeReflectionTransformer()
    {
        $this->assertInstanceOf(ReflectionTransformer::class, new Prefix('\other', ['a' => 'b']));
    }
}
