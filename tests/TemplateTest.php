<?php

declare(strict_types=1);

namespace Cekta\DI\Test;

use Cekta\DI\Template;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

class TemplateTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testFailBuffering(): void
    {
        $this->expectException(RuntimeException::class);
        $template = new Template(__DIR__ . '/../readme.md');
        $method = (new ReflectionClass($template))
            ->getMethod('handleResult');
        $method->setAccessible(true);
        $method->invoke($template, false);
    }
}
