<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Autowire\Reader\Exception;

use Cekta\DI\Provider\Autowire\Reader\Exception\InvalidClassName;
use PHPUnit\Framework\TestCase;

class InvalidClassNameTest extends TestCase
{
    public function testConstructor()
    {
        $e = new InvalidClassName('className');
        $this->assertSame('Invalid class name: `className`', $e->getMessage());
    }
}
