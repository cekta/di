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
        $data = [
            'apply and change dependencies' => [
                'pattern' => 'some\namespace\test',
                'transforms' => [
                    'source' => 'target',
                    'source2' => 'target2'
                ],
                'container' => 'some\namespace\test',
                'dependency_name' => 'source2',
                'expected' => 'target2'
            ],
            'not apply and not change' => [
                'pattern' => 'some invalid name',
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
    private function checkDataSet(
        string $pattern,
        array $transforms,
        string $container,
        string $dependency_name,
        string $expected
    ): void {
        $rule = new Equal($pattern, $transforms);
        $result = $rule->apply($container, $dependency_name);
        $this->assertSame($expected, $result);
    }
}
