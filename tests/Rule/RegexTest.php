<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Rule;

use Cekta\DI\Rule\Regex;
use PHPUnit\Framework\TestCase;

class RegexTest extends TestCase
{
    public function testApply(): void
    {
        $data = [
            'apply and change dependencies' => [
                'pattern' => '/test/',
                'transforms' => [
                    'source' => 'target',
                    'source2' => 'target2'
                ],
                'container' => 'some\namespace\test',
                'dependency_name' => 'source2',
                'expected' => 'target2',
            ],
            'not apply and not change' => [
                'pattern' => '/some invalid pattern/',
                'transforms' => [
                    'source' => 'target',
                    'source2' => 'target2'
                ],
                'container' => 'some\namespace\test',
                'dependency_name' => 'source2',
                'expected' => 'source2',
            ],
        ];
        foreach ($data as $dataset) {
            $this->checkDataSet(...$dataset);
        }
    }

    /**
     * @param string $pattern
     * @param array<string, string> $transforms
     * @param string $container
     * @param string $dependency_name
     * @param string $expected
     * @return void
     */
    public function checkDataSet(
        string $pattern,
        array $transforms,
        string $container,
        string $dependency_name,
        string $expected
    ): void {
        $rule = new Regex($pattern, $transforms);
        $this->assertSame($expected, $rule->apply($container, $dependency_name));
    }
}
