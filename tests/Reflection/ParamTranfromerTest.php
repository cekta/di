<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Reflection;

use Cekta\DI\Reflection\ParamTranfromer;
use PHPUnit\Framework\TestCase;

class ParamTranfromerTest extends TestCase
{
    public function testTransform()
    {
        $transformer = new ParamTranfromer('\prefix', ['a' => 'b']);
        $this->assertSame(['b', 'c'], $transformer->transform('\prefix\Some', ['a' , 'c']));
    }

    public function testTransformWithoutModification()
    {
        $transformer = new ParamTranfromer('\other', ['a' => 'b']);
        $this->assertSame(['a', 'c'], $transformer->transform('\prefix\Some', ['a' , 'c']));
    }
}
