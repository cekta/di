<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\DependencyMap\Dependency\Alias;
use Cekta\DI\DependencyMap\Dependency\AutowiringShared;
use Cekta\DI\DependencyMap\Dependency\Container;
use Cekta\DI\DependencyMap\Dependency\Param;

class ContainerGenerator
{
    public function __construct(
        private DependencyMap $dependency_map = new DependencyMap(),
        private Template $template = new Template(__DIR__ . '/../template/container.compiler.php'),
    ) {
    }

    public function generate(ManifestInterface $manifest): string
    {
        $definition = $manifest->definition();
        $dependency_map = $this->dependency_map->generate($manifest);
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
            'namespace' => $manifest->fqcn()->namespace,
            'class' => $manifest->fqcn()->className,
            'entries' => $definition->entries,
            'singletons' => $definition->singletons,
            'factories' => $definition->factories,
            'dependencies' => $dependencies,
            'required_keys' => $required_keys,
        ]);
    }
}
