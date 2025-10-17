<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Rule;

use Cekta\DI\DependencyDTO;
use Cekta\DI\Rule\Equal;
use PHPUnit\Framework\TestCase;

class EqualTest extends TestCase
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
                'pattern' => 'some\namespace\test',
                'transforms' => [
                    'source' => 'target',
                    'source2' => 'target2'
                ],
                'container' => 'some\namespace\test',
                'dependencies' => $dependencies,
                'expected' => [new DependencyDTO('example'), new DependencyDTO('target'), new DependencyDTO('target2')],
            ],
            'not apply and not change' => [
                'pattern' => 'some invalid name',
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
    private function checkDataSet(
        string $pattern,
        array $transforms,
        string $container,
        array $dependencies,
        array $expected
    ): void {
        $rule = new Equal($pattern, $transforms);
        $result = $rule->apply($container, $dependencies);
        foreach ($expected as $index => $dependency) {
            $this->assertSame($dependency->getName(), $result[$index]->getName());
        }
    }
}
