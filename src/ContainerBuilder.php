<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\DependencyMap\Dependency\Alias;
use Cekta\DI\DependencyMap\Dependency\AutowiringShared;
use Cekta\DI\DependencyMap\Dependency\Param;

readonly class ContainerBuilder
{
    private DependencyMap $dependency_map;
    private Template $template;
    public FQCN $fqcn;
    /**
     * @param array<string> $entries
     * @param array<string, mixed|Lazy> $params
     * @param array<string, string> $alias
     * @param string $fqcn
     * @param array<string> $singletons
     * @param array<string> $factories
     */
    public function __construct(
        public array $entries = [],
        public array $params = [],
        public array $alias = [],
        string $fqcn = 'App\Container',
        public array $singletons = [],
        public array $factories = [],
    ) {
        $this->fqcn = new FQCN($fqcn);
        $this->dependency_map = new DependencyMap();
        $this->template = new Template(__DIR__ . '/../template/container.compiler.php');
    }

    /**
     * @return string
     */
    public function build(): string
    {
        $dependency_map = $this->dependency_map->generate($this);

        $required_keys = [];
        $dependencies = [];
        foreach ($dependency_map as $dependency) {
            if ($dependency::class === Param::class) {
                $required_keys[] = $dependency->name;
            }
            if (
                in_array(
                    $dependency::class,
                    [DependencyMap\Dependency\Container::class, Alias::class, AutowiringShared::class]
                )
            ) {
                $dependencies[$dependency->name] = $dependency->render($dependency_map);
            }

            if ($dependency::class === Alias::class) {
                $dependencies[$dependency->target] = $dependency_map[$dependency->target]->render($dependency_map);
            }
        }
        return $this->template->render([
            'namespace' => $this->fqcn->namespace,
            'class' => $this->fqcn->className,
            'entries' => $this->entries,
            'singletons' => $this->singletons,
            'factories' => $this->factories,

            'dependencies' => $dependencies,
            'required_keys' => $required_keys,
        ]);
    }
}
