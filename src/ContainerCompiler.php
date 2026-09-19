<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\DependencyMap\Dependency\Alias;
use Cekta\DI\DependencyMap\Dependency\AutowiringShared;
use Cekta\DI\DependencyMap\Dependency\Container;
use Cekta\DI\DependencyMap\Dependency\Param;

class ContainerCompiler
{
    public function __construct(
        private DependencyMap $dependency_map = new DependencyMap(),
        private Template $template = new Template(__DIR__ . '/../template/container.compiler.php'),
    ) {
    }

    public function compile(AbstractProject $project): string
    {
        $definition = $project->definition();
        $config = new BuildConfiguration(
            fqcn:  new FQCN($project->fqcn),
            entries: $definition['entries'] ?? [],
            params: $project->params,
            alias: $definition['alias'] ?? [],
            singletons: $definition['singletons'] ?? [],
            factories: $definition['factories'] ?? [],
        );
        $dependency_map = $this->dependency_map->generate($config);
        $required_keys = [];
        $dependencies = [];
        foreach ($dependency_map as $dependency) {
            if ($dependency::class === Param::class) {
                $required_keys[] = $dependency->name;
            }
            if (
                in_array($dependency::class, [Container::class, Alias::class, AutowiringShared::class])
            ) {
                $dependencies[$dependency->name] = $dependency->render($dependency_map);
            }
            if ($dependency::class === Alias::class) {
                $dependencies[$dependency->target] = $dependency_map[$dependency->target]->render($dependency_map);
            }
        }
        return $this->template->render([
            'namespace' => $config->fqcn->namespace,
            'class' => $config->fqcn->className,
            'entries' => $config->entries,
            'singletons' => $config->singletons,
            'factories' => $config->factories,
            'dependencies' => $dependencies,
            'required_keys' => $required_keys,
        ]);
    }
}
