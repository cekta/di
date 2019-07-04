<?php
declare(strict_types=1);

namespace Cekta\DI\Test\Unit\Provider\Exception;

use Cekta\DI\Provider\Exception\NotFound;
use PHPUnit\Framework\TestCase;

class NotFoundTest extends TestCase
{
    public function testMessage()
    {
        $e = new NotFound('id');
        $this->assertSame('Container `id` not found', $e->getMessage());
    }
}
