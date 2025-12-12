<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\Exception\CircularDependency;
use Cekta\DI\Exception\NotInstantiable;

/**
 * @external
 */
class Configuration
{
    private InternalCompiler $internal_compiler;
    private Template $template;

    /**
     * @param array<string> $containers
     * @param array<string, mixed|Lazy> $params
     * @param array<string, string> $alias
     * @param string $fqcn
     * @param array<string> $singletons
     * @param array<string> $factories
     */
    public function __construct(
        public readonly array $containers = [],
        public readonly array $params = [],
        public readonly array $alias = [],
        public readonly string $fqcn = 'App\Container',
        public readonly array $singletons = [],
        public readonly array $factories = [],
    ) {
        $this->internal_compiler = new InternalCompiler();
        $this->template = new Template(__DIR__ . '/../template/container.compiler.php');
    }

    /**
     * toString() ?
     * @return string
     * @throws CircularDependency
     * @throws NotInstantiable
     */
    public function compile(): string
    {
        [$dependencies, $required_keys] = ($this->internal_compiler)($this);
        $fqcn = new FQCN($this->fqcn);
        return $this->template->render([
            'namespace' => $fqcn->namespace,
            'class' => $fqcn->className,
            'targets' => $this->containers,
            'dependencies' => $dependencies,
            'alias' => $this->alias,
            'required_keys' => $required_keys,
            'singletons' => $this->singletons,
            'factories' => $this->factories,
        ]);
    }
}
