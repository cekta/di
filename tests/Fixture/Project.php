<?php

declare(strict_types=1);

namespace Cekta\DI\Test\Fixture;

use Cekta\DI\AbstractProject;
use stdClass;

class Project extends AbstractProject
{
    /**
     * @param array<string> $entries
     * @param array<string, string> $alias
     * @param array<string> $singletons
     * @param array<string> $factories
     */
    public function __construct(
        string $filename,
        string $fqcn,
        array $params = [],
        public array $entries = [],
        public array $alias = [],
        public readonly array $singletons = [],
        public readonly array $factories = [],
    ) {
        parent::__construct($filename, $fqcn, $params);
    }

    public function definition(): array
    {
        return [
            'entries' => $this->entries,
            'alias' => $this->alias,
            'singletons' => $this->singletons,
            'factories' => $this->factories,
        ];
    }
}
