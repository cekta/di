<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Rule;

use Cekta\DI\DependencyDTO;
use Cekta\DI\Rule\Regex;
use PHPUnit\Framework\TestCase;

class RegexTest extends TestCase
{
    public function testApply(): void
    {
        $dependencies = [
            new DependencyDTO('example'),
            new DependencyDTO('source'),
            new DependencyDTO('source2')
        ];
        $data = [
            'apply and change dependencies' => [
                'pattern' => '/test/',
                'transforms' => [
                    'source' => 'target',
                    'source2' => 'target2'
                ],
                'container' => 'some\namespace\test',
                'dependencies' => $dependencies,
                'expected' => [new DependencyDTO('example'), new DependencyDTO('target'), new DependencyDTO('target2')],
            ],
            'not apply and not change' => [
                'pattern' => '/some invalid pattern/',
                'transforms' => [
                    'source' => 'target',
                    'source2' => 'target2'
                ],
                'container' => 'some\namespace\test',
                'dependencies' => $dependencies,
                'expected' => $dependencies,
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
     * @param DependencyDTO[] $dependencies
     * @param DependencyDTO[] $expected
     * @return void
     */
    public function checkDataSet(
        string $pattern,
        array $transforms,
        string $container,
        array $dependencies,
        array $expected
    ): void {
        $rule = new Regex($pattern, $transforms);
        $result = $rule->apply($container, $dependencies);
        foreach ($expected as $index => $dependency) {
            $this->assertSame($dependency->getName(), $result[$index]->getName());
        }
    }
}
