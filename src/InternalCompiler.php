<?php

declare(strict_types=1);

namespace Cekta\DI;

use Cekta\DI\DependencyMap\Dependency\Alias;
use Cekta\DI\DependencyMap\Dependency\AutowiringShared;
use Cekta\DI\DependencyMap\Dependency\Param;

class InternalCompiler
{
    private DependencyMap $dependency_map;
    private Template $template;

    public function __construct()
    {
        $this->dependency_map = new DependencyMap();
        $this->template = new Template(__DIR__ . '/../template/container.compiler.php');
    }

    public function generate(Compiler $configuration): string
    {
        $dependency_map = $this->dependency_map->generate($configuration);

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
            'namespace' => $configuration->fqcn->namespace,
            'class' => $configuration->fqcn->className,
            'targets' => $configuration->containers,
            'singletons' => $configuration->singletons,
            'factories' => $configuration->factories,

            'dependencies' => $dependencies,
            'required_keys' => $required_keys,
        ]);
    }
}
